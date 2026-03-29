<?php

namespace Database\Factories;

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\DetectionEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetectionEvent>
 */
class DetectionEventFactory extends Factory
{
    protected $model = DetectionEvent::class;

    public function definition(): array
    {
        return [
            'bin_id' => Bin::factory(),
            'waste_type' => fake()->randomElement(WasteType::cases()),
            'confidence' => fake()->numberBetween(70, 99),
            'image_path' => null,
            'detected_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }

    public function ofType(WasteType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'waste_type' => $type,
        ]);
    }

    public function highConfidence(): static
    {
        return $this->state(fn (array $attributes) => [
            'confidence' => fake()->numberBetween(90, 99),
        ]);
    }

    public function lowConfidence(): static
    {
        return $this->state(fn (array $attributes) => [
            'confidence' => fake()->numberBetween(50, 70),
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'detected_at' => fake()->dateTimeBetween('today', 'now'),
        ]);
    }

    public function withImage(): static
    {
        return $this->state(function (array $attributes) {
            $binId = $attributes['bin_id'];
            $timestamp = now()->timestamp;

            return [
                'image_path' => "detections/{$binId}/{$timestamp}.jpg",
            ];
        });
    }
}
