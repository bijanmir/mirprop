<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add middleware aliases
        $middleware->alias([
            'ensure.organization' => \App\Http\Middleware\EnsureUserHasOrganization::class,
            'webhook.stripe' => \App\Http\Middleware\VerifyStripeWebhookSignature::class,
        ]);

        // Add middleware to web group if needed
        $middleware->web(append: [
            // Add any custom web middleware here
        ]);

        // Configure API middleware
        $middleware->api(prepend: [
            // Add any custom API middleware here
        ]);

        // Exclude CSRF for webhooks
        $middleware->validateCsrfTokens(except: [
            'webhooks/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();