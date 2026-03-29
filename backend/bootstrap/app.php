<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Logging\WideEvent;
use App\Logging\WideEventMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(WideEventMiddleware::class);

        // Register 'role' as a middleware alias so we can use ->middleware('role:admin')
        // in routes. This is how Laravel 12 registers middleware — no Kernel.php needed.
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Sanctum: make API routes stateful so cookies work from the same domain.
        // This is needed if you ever test API routes from the browser too.
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e) {
            $wideEvent = app(WideEvent::class);
            $includeStack = config('wide-events.error.include_stack', false);
            $wideEvent->captureError($e, $includeStack);
        });
    })->create();
