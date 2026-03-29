<?php

namespace App\Console\Commands;

use App\Models\Zone;
use App\Services\RouteOptimizationService;
use Illuminate\Console\Command;

class GenerateCollectionRoutes extends Command
{
    protected $signature = 'route:generate {--zone= : Specific zone slug to generate for}';

    protected $description = 'Generate optimized collection routes for zones with bins ready for pickup';

    public function handle(RouteOptimizationService $service): int
    {
        $query = Zone::query()->where('is_active', true);

        if ($slug = $this->option('zone')) {
            $query->where('slug', $slug);
        }

        $zones = $query->get();

        if ($zones->isEmpty()) {
            $this->warn('No active zones found.');

            return self::SUCCESS;
        }

        $generated = 0;

        foreach ($zones as $zone) {
            $this->info("Checking zone: {$zone->name}...");

            $route = $service->generateRoute($zone);

            if ($route) {
                $stopCount = $route->totalStopsCount();
                $this->info("  → Route #{$route->id} generated: {$stopCount} stops, {$route->total_distance_km} km");
                $generated++;
            } else {
                $this->line('  → Not enough eligible bins or no available collector.');
            }
        }

        $this->info("{$generated} route(s) generated.");

        return self::SUCCESS;
    }
}
