<?php

namespace App\Logging;

use App\Logging\Filters\PiiFilter;
use App\Logging\Sampling\TailSampler;
use Illuminate\Support\ServiceProvider;

class WideEventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/wide-events.php',
            'wide-events'
        );

        // Scoped = fresh instance per request lifecycle (Octane-safe)
        $this->app->scoped(WideEvent::class, function ($app) {
            return new WideEvent($app['request'] ?? null);
        });

        // Singleton so terminate() uses the same middleware instance as handle()
        $this->app->singleton(WideEventMiddleware::class);

        // Singletons for sampling and filtering
        $this->app->singleton(TailSampler::class);
        $this->app->singleton(PiiFilter::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/wide-events.php' => config_path('wide-events.php'),
        ], 'wide-events-config');
    }
}
