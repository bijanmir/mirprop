<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStripeWebhook;
use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Stripe\Webhook;

class WebhookController extends Controller
{
    public function stripe(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            return response('Invalid signature', 400);
        }
        
        // Check for duplicate processing
        $webhookEvent = WebhookEvent::firstOrCreate([
            'type' => 'stripe',
            'processor_id' => $event->id,
        ], [
            'payload' => $event,
            'status' => 'pending',
        ]);
        
        if ($webhookEvent->wasRecentlyCreated) {
            ProcessStripeWebhook::dispatch($event);
        }
        
        return response()->json(['status' => 'ok']);
    }
}