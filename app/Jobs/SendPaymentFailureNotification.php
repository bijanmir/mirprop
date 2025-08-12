<?php

namespace App\Jobs;
use App\Mail\PaymentFailedMail;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class SendPaymentFailureNotification implements ShouldQueue
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
        $this->payment->load(['lease.unit.property', 'contact']);    // Check if contact has email
        if (!$this->payment->contact->email) {
            Log::warning('Cannot send payment failure notification - no email address', [
                'payment_id' => $this->payment->id,
                'contact_id' => $this->payment->contact_id
            ]);
            return;
        }
        try {
            Mail::to($this->payment->contact->email)
                ->queue(new PaymentFailedMail($this->payment));        // Also notify property manager
            if ($this->payment->lease && $this->payment->lease->organization) {
                $managers = $this->payment->lease->organization->users()
                    ->wherePivotIn('role', ['owner', 'manager'])
                    ->get();
                foreach ($managers as $manager) {
                    Mail::to($manager->email)
                        ->queue(new PaymentFailedMail($this->payment, true));
                }
            }
            Log::info('Payment failure notification queued', [
                'payment_id' => $this->payment->id,
                'recipient' => $this->payment->contact->email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue payment failure notification', [
                'payment_id' => $this->payment->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    public function failed(\Throwable $exception): void
    {
        Log::error('Failed to send payment failure notification after all retries', [
            'payment_id' => $this->payment->id,
            'error' => $exception->getMessage()
        ]);
    }
}
