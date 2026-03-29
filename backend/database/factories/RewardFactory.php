<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reward>
 */
class RewardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'brand_id' => null,
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'points_cost' => fake()->randomElement([50, 100, 150, 200, 300, 500]),
            'stock' => fake()->optional(0.7)->numberBetween(5, 100),
            'active' => true,
            'sort_order' => 0,
        ];
    }

    public function forBrand(Brand $brand): static
    {
        return $this->state(fn () => [
            'brand_id' => $brand->id,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => [
            'stock' => 0,
        ]);
    }
}
