<?php

namespace Database\Factories;

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BinAssignment>
 */
class BinAssignmentFactory extends Factory
{
    protected $model = BinAssignment::class;

    public function definition(): array
    {
        return [
            'bin_id' => Bin::factory(),
            'outlet_id' => Outlet::factory(),
            'assigned_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'unassigned_at' => null,
        ];
    }

    public function historical(): static
    {
        return $this->state(function (array $attributes) {
            $assignedAt = $attributes['assigned_at'] ?? fake()->dateTimeBetween('-6 months', '-1 month');

            return [
                'assigned_at' => $assignedAt,
                'unassigned_at' => fake()->dateTimeBetween($assignedAt, 'now'),
            ];
        });
    }

    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'unassigned_at' => null,
        ]);
    }
}
