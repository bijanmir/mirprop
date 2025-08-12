<?php
namespace App\Jobs;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class RetryFailedPayments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 1;
    public int $timeout = 300;

    public function handle(PaymentService $paymentService): void
    {
        $startTime = now();
        $retriedCount = 0;

        Log::info('Starting failed payment retry job');

        // Get retryable failed payments
        $retryablePayments = Payment::where('status', 'failed')
            ->whereIn('failure_reason', ['insufficient_funds', 'temporary_hold', 'card_declined'])
            ->where('created_at', '>=', now()->subDays(7))
            ->whereNull('retry_attempted_at')
            ->with(['lease', 'contact'])
            ->chunk(50, function ($payments) use ($paymentService, &$retriedCount) {
                foreach ($payments as $payment) {
                    try {
                        $this->attemptPaymentRetry($payment, $paymentService);
                        $retriedCount++;
                    } catch (\Exception $e) {
                        Log::error('Failed to retry payment', [
                            'payment_id' => $payment->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

        $duration = now()->diffInSeconds($startTime);

        Log::info('Failed payment retry job completed', [
            'payments_retried' => $retriedCount,
            'duration_seconds' => $duration
        ]);
    }

    private function attemptPaymentRetry(Payment $payment, PaymentService $paymentService): void
    {
        // Skip if lease is no longer active
        if (!$payment->lease || $payment->lease->status !== 'active') {
            Log::info('Skipping retry - lease no longer active', [
                'payment_id' => $payment->id
            ]);
            return;
        }

        try {
            // Create new payment intent for retry
            $paymentIntent = $paymentService->createPaymentIntent(
                $payment->lease,
                $payment->amount_cents,
                'Retry: ' . ($payment->description ?? 'Rent payment')
            );

            // Send retry notification to tenant
            SendPaymentRetryNotification::dispatch($payment, $paymentIntent);

            // Mark original payment as retry attempted
            $payment->update([
                'retry_attempted_at' => now(),
                'meta' => array_merge($payment->meta ?? [], [
                    'retry_payment_intent_id' => $paymentIntent->id,
                    'retry_attempted_at' => now()->toIso8601String()
                ])
            ]);

            Log::info('Payment retry initiated', [
                'original_payment_id' => $payment->id,
                'new_payment_intent_id' => $paymentIntent->id,
                'amount_cents' => $payment->amount_cents
            ]);

        } catch (\Exception $e) {
            Log::error('Payment retry failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            // Mark as retry attempted even if it failed
            $payment->update(['retry_attempted_at' => now()]);

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Failed payment retry job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}