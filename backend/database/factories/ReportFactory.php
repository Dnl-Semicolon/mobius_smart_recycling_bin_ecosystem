<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Bin;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    protected $model = Report::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bin_id' => Bin::factory(),
            'type' => fake()->randomElement(ReportType::cases()),
            'description' => fake()->sentence(10),
            'status' => ReportStatus::Open,
            'resolution' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => ReportStatus::Resolved,
            'resolution' => fake()->sentence(8),
            'resolved_by' => User::factory()->admin(),
            'resolved_at' => now(),
        ]);
    }

    public function investigating(): static
    {
        return $this->state(fn () => [
            'status' => ReportStatus::Investigating,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => ReportStatus::Closed,
            'resolution' => fake()->sentence(8),
            'resolved_by' => User::factory()->admin(),
            'resolved_at' => now()->subDay(),
        ]);
    }
}
