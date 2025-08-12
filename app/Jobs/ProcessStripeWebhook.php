<?php

namespace App\Jobs;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class ProcessStripeWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;
    public int $timeout = 120;
    public function __construct(
        public array $event
    ) 
    {
    }
    public function handle(PaymentService $paymentService): void
    {
        Log::info('Processing Stripe webhook', [
            'event_type' => $this->event['type'],
            'event_id' => $this->event['id'] ?? null
        ]);
        try {
            match ($this->event['type']) {
                'payment_intent.succeeded' => $paymentService->handlePaymentSuccess($this->event['data']['object']),
                'payment_intent.payment_failed' => $paymentService->handlePaymentFailure($this->event['data']['object']),
                'payment_intent.processing' => $paymentService->handlePaymentProcessing($this->event['data']['object']),
                'charge.refunded' => $this->handleRefund($this->event['data']['object']),
                'customer.subscription.created' => $this->handleSubscriptionCreated($this->event['data']['object']),
                'customer.subscription.deleted' => $this->handleSubscriptionCanceled($this->event['data']['object']),
                default => Log::info('Unhandled webhook event type', ['type' => $this->event['type']])
            };
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'event_type' => $this->event['type'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    private function handleRefund(array $charge): void
    {
        Log::info('Processing refund', ['charge_id' => $charge['id']]);
        // TODO: Implement refund logic
    }
    private function handleSubscriptionCreated(array $subscription): void
    {
        Log::info('Processing subscription created', ['subscription_id' => $subscription['id']]);
        // TODO: Implement subscription creation logic
    }
    private function handleSubscriptionCanceled(array $subscription): void
    {
        Log::info('Processing subscription canceled', ['subscription_id' => $subscription['id']]);
        // TODO: Implement subscription cancellation logic
    }
    public function failed(\Throwable $exception): void
    {
        Log::error('Stripe webhook job failed after all retries', [
            'event_type' => $this->event['type'] ?? 'unknown',
            'error' => $exception->getMessage()
        ]);
    }
}
