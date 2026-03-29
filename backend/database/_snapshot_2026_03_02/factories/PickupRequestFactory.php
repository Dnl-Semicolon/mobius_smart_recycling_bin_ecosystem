<?php

namespace Database\Factories;

use App\Enums\PickupStatus;
use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PickupRequest>
 */
class PickupRequestFactory extends Factory
{
    protected $model = PickupRequest::class;

    public function definition(): array
    {
        return [
            'bin_id' => Bin::factory(),
            'status' => PickupStatus::Pending,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PickupStatus::Pending,
        ]);
    }

    public function claimed(?User $collector = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PickupStatus::Claimed,
            'claimed_by' => $collector?->id ?? User::factory()->collector(),
            'claimed_at' => now(),
        ]);
    }

    public function completed(?User $collector = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PickupStatus::Completed,
            'claimed_by' => $collector?->id ?? User::factory()->collector(),
            'claimed_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PickupStatus::Cancelled,
        ]);
    }
}
