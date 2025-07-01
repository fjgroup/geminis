<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin'             => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'admin.security'    => \App\Http\Middleware\AdminSecurityMiddleware::class,
            'admin.or.reseller' => \App\Http\Middleware\EnsureUserIsAdminOrReseller::class,
            'inject.context'    => \App\Http\Middleware\InjectResellerContext::class,
            'input.sanitize'    => \App\Http\Middleware\InputSanitizationMiddleware::class,
            'role.reseller'     => \App\Http\Middleware\EnsureUserIsReseller::class,
            'reseller.security' => \App\Http\Middleware\ResellerSecurityMiddleware::class,
            'client'            => \App\Http\Middleware\EnsureUserIsClient::class,
            'cart.ratelimit'    => \App\Http\Middleware\CartRateLimitMiddleware::class,
            'verified'          => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Add CSRF token validation exception for PayPal webhook
        $middleware->validateCsrfTokens(except: [
            'webhooks/paypal',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
