<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CollectorAgency>
 */
class CollectorAgencyFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'contact_person' => fake()->name(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'description' => fake()->sentence(),
            'fleet_size' => fake()->numberBetween(1, 50),
            'coverage_area' => fake()->city().' area',
            'status' => ApplicationStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => ApplicationStatus::Pending,
        ]);
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ApplicationStatus::Approved,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => ApplicationStatus::Rejected,
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
