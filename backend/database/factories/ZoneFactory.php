<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Zone>
 */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        $name = fake()->unique()->city().' '.fake()->randomElement(['North', 'South', 'Central', 'East', 'West']);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'region' => 'Penang Island',
            'boundary' => [
                [5.40, 100.30],
                [5.45, 100.30],
                [5.45, 100.35],
                [5.40, 100.35],
            ],
            'depot_latitude' => 5.3784,
            'depot_longitude' => 100.3354,
            'depot_name' => 'Mobius Recycling Hub',
            'min_bins_for_dispatch' => 3,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withMinBins(int $min): static
    {
        return $this->state(fn (array $attributes) => [
            'min_bins_for_dispatch' => $min,
        ]);
    }
}
