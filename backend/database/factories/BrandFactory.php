<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'organization_id' => Organization::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'logo_path' => 'brands/placeholder.png',
            'description' => fake()->sentence(),
            'website' => fake()->url(),
            'point_multiplier' => 1.00,
            'is_active' => true,
        ];
    }

    public function sponsored(float $multiplier = 1.50): static
    {
        return $this->state(fn () => [
            'point_multiplier' => $multiplier,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
