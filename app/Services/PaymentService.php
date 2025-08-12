<?php

namespace App\Services;

use App\Jobs\SendPaymentFailureNotification;
use App\Jobs\SendPaymentReceipt;
use App\Models\ActivityLog;
use App\Models\Lease;
use App\Models\LeaseCharge;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class PaymentService
{
    protected StripeClient $stripe;
    
    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }
    
    public function createPaymentIntent(Lease $lease, int $amountCents, string $description = null): \Stripe\PaymentIntent
    {
        $metadata = [
            'lease_id' => $lease->id,
            'organization_id' => $lease->organization_id,
            'tenant_id' => $lease->primary_contact_id,
        ];
        
        $paymentIntent = $this->stripe->paymentIntents->create([
            'amount' => $amountCents,
            'currency' => 'usd',
            'payment_method_types' => ['us_bank_account', 'card'],
            'metadata' => $metadata,
            'description' => $description ?? "Rent payment for {$lease->unit->property->name} - {$lease->unit->label}",
        ]);
        
        // Store pending payment record
        Payment::create([
            'organization_id' => $lease->organization_id,
            'lease_id' => $lease->id,
            'contact_id' => $lease->primary_contact_id,
            'amount_cents' => $amountCents,
            'processor_id' => $paymentIntent->id,
            'status' => 'pending',
        ]);
        
        return $paymentIntent;
    }
    
    public function handlePaymentSuccess(array $paymentIntentData): void
    {
        $paymentIntentId = $paymentIntentData['id'];
        $metadata = $paymentIntentData['metadata'];
        
        DB::transaction(function () use ($paymentIntentData, $metadata, $paymentIntentId) {
            $payment = Payment::where('processor_id', $paymentIntentId)->firstOrFail();
            
            $payment->update([
                'status' => 'succeeded',
                'method' => $this->determinePaymentMethod($paymentIntentData),
                'posted_at' => now(),
            ]);
            
            // Update lease balance
            $this->updateLeaseBalance($payment);
            
            // Send receipt email
            SendPaymentReceipt::dispatch($payment);
            
            // Log activity
            ActivityLog::create([
                'organization_id' => $payment->organization_id,
                'actor_id' => $payment->contact_id,
                'entity_type' => Payment::class,
                'entity_id' => $payment->id,
                'action' => 'payment.succeeded',
                'diff' => [
                    'amount_cents' => $payment->amount_cents,
                    'method' => $payment->method,
                ]
            ]);
        });
    }
    
    public function handlePaymentFailure(array $paymentIntentData): void
    {
        $paymentIntentId = $paymentIntentData['id'];
        $lastPaymentError = $paymentIntentData['last_payment_error'] ?? null;
        
        $payment = Payment::where('processor_id', $paymentIntentId)->firstOrFail();
        
        $payment->update([
            'status' => 'failed',
            'failure_reason' => $lastPaymentError['decline_code'] ?? 'unknown',
            'failure_message' => $lastPaymentError['message'] ?? 'Payment failed',
        ]);
        
        // Notify tenant of failure
        SendPaymentFailureNotification::dispatch($payment);
        
        Log::warning('Payment failed', [
            'payment_id' => $payment->id,
            'reason' => $payment->failure_reason,
            'message' => $payment->failure_message,
        ]);
    }
    
    public function handlePaymentProcessing(array $paymentIntentData): void
    {
        $paymentIntentId = $paymentIntentData['id'];
        
        $payment = Payment::where('processor_id', $paymentIntentId)->firstOrFail();
        
        $payment->update([
            'status' => 'processing',
            'method' => $this->determinePaymentMethod($paymentIntentData),
        ]);
        
        Log::info('Payment processing', ['payment_id' => $payment->id]);
    }
    
    private function updateLeaseBalance(Payment $payment): void
    {
        $lease = $payment->lease;
        
        // Apply payment to outstanding charges
        $outstandingCharges = LeaseCharge::where('lease_id', $lease->id)
            ->where('balance_cents', '>', 0)
            ->orderBy('due_date')
            ->get();
        
        $remainingPayment = $payment->amount_cents;
        
        foreach ($outstandingCharges as $charge) {
            if ($remainingPayment <= 0) break;
            
            $amountToApply = min($remainingPayment, $charge->balance_cents);
            
            $charge->decrement('balance_cents', $amountToApply);
            $remainingPayment -= $amountToApply;
            
            // Create payment allocation record
            PaymentAllocation::create([
                'payment_id' => $payment->id,
                'lease_charge_id' => $charge->id,
                'amount_cents' => $amountToApply,
            ]);
        }
        
        // If payment exceeds charges, create credit balance
        if ($remainingPayment > 0) {
            LeaseCharge::create([
                'lease_id' => $lease->id,
                'type' => 'credit',
                'amount_cents' => -$remainingPayment,
                'description' => 'Overpayment credit',
                'due_date' => now(),
                'balance_cents' => -$remainingPayment,
            ]);
        }
    }
    
    public function calculateOutstandingBalance(Lease $lease): int
    {
        return LeaseCharge::where('lease_id', $lease->id)
            ->where('balance_cents', '>', 0)
            ->sum('balance_cents');
    }
    
    private function determinePaymentMethod(array $paymentIntentData): string
    {
        $charges = $paymentIntentData['charges']['data'] ?? [];
        
        if (empty($charges)) {
            return 'unknown';
        }
        
        $charge = $charges[0];
        $paymentMethodDetails = $charge['payment_method_details'] ?? [];
        
        if (isset($paymentMethodDetails['us_bank_account'])) {
            return 'ach';
        }
        
        if (isset($paymentMethodDetails['card'])) {
            return 'card';
        }
        
        return 'unknown';
    }
}