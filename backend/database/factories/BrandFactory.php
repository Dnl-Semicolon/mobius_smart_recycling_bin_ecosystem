<?php

namespace Database\Factories;

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
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'primary_color' => fake()->hexColor(),
            'points_multiplier' => 1.00,
            'rewards_budget' => 0,
            'active' => true,
        ];
    }

    public function sponsored(float $multiplier = 1.50, int $budget = 10000): static
    {
        return $this->state(fn () => [
            'points_multiplier' => $multiplier,
            'rewards_budget' => $budget,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'active' => false,
            'contact_person' => fake()->name(),
            'contact_email' => fake()->safeEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'website_url' => fake()->url(),
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => 'approved',
            'active' => true,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
            'active' => false,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
