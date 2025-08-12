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
use Stripe\StripeClient;
class CheckACHPaymentStatus implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
public int $tries = 3;
public int $timeout = 30;

public function __construct(
    public Payment $payment
) {}

public function handle(PaymentService $paymentService): void
{
    // Only check payments that are still processing
    if ($this->payment->status !== 'processing') {
        Log::info('Payment no longer in processing status', [
            'payment_id' => $this->payment->id,
            'current_status' => $this->payment->status
        ]);
        return;
    }

    try {
        $stripe = new StripeClient(config('services.stripe.secret'));
        
        // Retrieve the payment intent from Stripe
        $paymentIntent = $stripe->paymentIntents->retrieve($this->payment->processor_id);
        
        Log::info('Retrieved payment intent status', [
            'payment_id' => $this->payment->id,
            'stripe_status' => $paymentIntent->status
        ]);

        // Update payment based on Stripe status
        switch ($paymentIntent->status) {
            case 'succeeded':
                $paymentService->handlePaymentSuccess($paymentIntent->toArray());
                break;
                
            case 'payment_failed':
            case 'canceled':
                $paymentService->handlePaymentFailure($paymentIntent->toArray());
                break;
                
            case 'processing':
                // Still processing - schedule another check in 24 hours
                self::dispatch($this->payment)->delay(now()->addDay());
                Log::info('Payment still processing, checking again in 24 hours', [
                    'payment_id' => $this->payment->id
                ]);
                break;
                
            default:
                Log::warning('Unexpected payment intent status', [
                    'payment_id' => $this->payment->id,
                    'status' => $paymentIntent->status
                ]);
        }
    } catch (\Exception $e) {
        Log::error('Failed to check ACH payment status', [
            'payment_id' => $this->payment->id,
            'error' => $e->getMessage()
        ]);
        
        throw $e;
    }
}

public function failed(\Throwable $exception): void
{
    Log::error('Failed to check ACH payment status after all retries', [
        'payment_id' => $this->payment->id,
        'error' => $exception->getMessage()
    ]);
}
}