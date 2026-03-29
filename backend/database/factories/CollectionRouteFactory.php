<?php

namespace Database\Factories;

use App\Enums\RouteStatus;
use App\Models\CollectionRoute;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionRoute>
 */
class CollectionRouteFactory extends Factory
{
    protected $model = CollectionRoute::class;

    public function definition(): array
    {
        return [
            'zone_id' => Zone::factory(),
            'collector_id' => User::factory()->collector(),
            'status' => RouteStatus::Pending,
            'stops' => [
                [
                    'order' => 1,
                    'bin_id' => 1,
                    'pickup_request_id' => null,
                    'outlet_name' => 'Starbucks Gurney Plaza',
                    'address' => 'Gurney Plaza, Penang',
                    'latitude' => 5.4370,
                    'longitude' => 100.3100,
                    'fill_level' => 92,
                    'service_time_sec' => 300,
                    'eta_minutes' => 12,
                    'arrival_distance_km' => 2.4,
                    'status' => 'pending',
                    'completed_at' => null,
                ],
            ],
            'total_distance_km' => fake()->randomFloat(2, 5, 30),
            'total_duration_min' => fake()->numberBetween(15, 90),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RouteStatus::Pending,
        ]);
    }

    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RouteStatus::Accepted,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RouteStatus::Active,
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RouteStatus::Completed,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RouteStatus::Cancelled,
        ]);
    }

    public function withStops(array $stops): static
    {
        return $this->state(fn (array $attributes) => [
            'stops' => $stops,
        ]);
    }
}
