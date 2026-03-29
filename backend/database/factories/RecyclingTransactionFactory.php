<?php

namespace Database\Factories;

use App\Models\DetectionEvent;
use App\Models\RecyclingTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecyclingTransaction>
 */
class RecyclingTransactionFactory extends Factory
{
    protected $model = RecyclingTransaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'detection_event_id' => DetectionEvent::factory(),
            'points_earned' => fake()->randomElement([2, 3, 5, 10]),
            'type' => 'earned',
            'description' => fake()->sentence(),
        ];
    }

    public function earned(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'earned',
        ]);
    }

    public function bonus(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bonus',
        ]);
    }
}
