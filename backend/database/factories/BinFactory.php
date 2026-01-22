<?php

namespace Database\Factories;

use App\Enums\BinStatus;
use App\Models\Bin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bin>
 */
class BinFactory extends Factory
{
    protected $model = Bin::class;

    public function definition(): array
    {
        return [
            'serial_number' => 'MBR-'.date('Y').'-'.str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'fill_level' => fake()->numberBetween(0, 100),
            'status' => fake()->randomElement(BinStatus::cases()),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BinStatus::Active,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BinStatus::Inactive,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BinStatus::Maintenance,
        ]);
    }

    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'fill_level' => 0,
        ]);
    }

    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'fill_level' => fake()->numberBetween(80, 100),
        ]);
    }

    public function withFillLevel(int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'fill_level' => $level,
        ]);
    }
}
