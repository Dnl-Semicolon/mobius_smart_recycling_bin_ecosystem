<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        $phonePrefixes = ['010', '011', '012', '013', '014', '016', '017', '018', '019'];

        $lat = fake()->latitude(3.0, 3.3);
        $lng = fake()->longitude(101.4, 101.8);

        return [
            'name' => fake()->company().' '.fake()->randomElement(['Cafe', 'Coffee', 'Tea House', 'Food Court']),
            'address' => fake()->streetAddress().', '.fake()->randomElement(['50000 Kuala Lumpur', '47500 Subang Jaya', '46000 Petaling Jaya', '43000 Kajang']),
            'latitude' => $lat,
            'longitude' => $lng,
            'contact_name' => fake()->name(),
            'contact_phone' => fake()->randomElement($phonePrefixes).'-'.fake()->numerify('### ####'),
            'contact_email' => fake()->safeEmail(),
            'operating_hours' => fake()->randomElement(['10:00-22:00', '08:00-20:00', '09:00-21:00', '24 hours']),
            'contract_status' => fake()->randomElement(ContractStatus::cases()),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_status' => ContractStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_status' => ContractStatus::Inactive,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'contract_status' => ContractStatus::Pending,
        ]);
    }
}
