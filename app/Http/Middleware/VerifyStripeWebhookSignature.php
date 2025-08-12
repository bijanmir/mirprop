<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class VerifyStripeWebhookSignature
{
    public function handle(Request $request, Closure $next)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        if (!$signature || !$secret) {
            return response('Missing signature or secret', 400);
        }

        try {
            // Verify the webhook signature
            Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        } catch (\Exception $e) {
            return response('Invalid payload', 400);
        }

        return $next($request);
    }
}