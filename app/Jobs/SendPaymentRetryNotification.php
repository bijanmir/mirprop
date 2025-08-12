<?php
namespace App\Jobs;
use App\Mail\PaymentRetryMail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\PaymentIntent;
class SendPaymentRetryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public Payment $payment,
        public PaymentIntent $paymentIntent
    ) {
    }

    public function handle(): void
    {
        // Load relationships
        $this->payment->load(['lease.unit.property', 'contact']);

        // Check if contact has email
        if (!$this->payment->contact->email) {
            Log::warning('Cannot send payment retry notification - no email address', [
                'payment_id' => $this->payment->id,
                'contact_id' => $this->payment->contact_id
            ]);
            return;
        }

        try {
            Mail::to($this->payment->contact->email)
                ->queue(new PaymentRetryMail($this->payment, $this->paymentIntent));

            Log::info('Payment retry notification queued', [
                'payment_id' => $this->payment->id,
                'new_payment_intent_id' => $this->paymentIntent->id,
                'recipient' => $this->payment->contact->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue payment retry notification', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}