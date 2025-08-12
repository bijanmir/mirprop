<?php

namespace App\Jobs;
use App\Mail\PaymentReceiptMail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class SendPaymentReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $timeout = 30;
    public function __construct(
        public Payment $payment
    ) {
    }
    public function handle(): void
    {
        // Load relationships
        $this->payment->load(['lease.unit.property', 'contact', 'allocations.leaseCharge']);    // Check if contact has email
        if (!$this->payment->contact->email) {
            Log::warning('Cannot send payment receipt - no email address', [
                'payment_id' => $this->payment->id,
                'contact_id' => $this->payment->contact_id
            ]);
            return;
        }
        try {
            Mail::to($this->payment->contact->email)
                ->queue(new PaymentReceiptMail($this->payment));
            Log::info('Payment receipt email queued', [
                'payment_id' => $this->payment->id,
                'recipient' => $this->payment->contact->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue payment receipt email', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send payment receipt after all retries', [
            'payment_id' => $this->payment->id,
            'error' => $exception->getMessage()
        ]);
    }
}