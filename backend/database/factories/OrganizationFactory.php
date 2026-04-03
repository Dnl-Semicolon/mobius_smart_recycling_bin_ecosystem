<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'type' => fake()->randomElement(['beverage_company', 'recycling_company', 'government']),
            'description' => fake()->sentence(),
            'logo_path' => 'organizations/placeholder.png',
            'website' => fake()->url(),
            'is_active' => true,
        ];
    }

    public function beverageCompany(): static
    {
        return $this->state(fn () => ['type' => 'beverage_company']);
    }

    public function recyclingCompany(): static
    {
        return $this->state(fn () => ['type' => 'recycling_company']);
    }
}
