<?php

namespace Database\Factories;

use App\Enums\NotificationType;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AppNotification>
 */
class AppNotificationFactory extends Factory
{
    protected $model = AppNotification::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(NotificationType::cases()),
            'title' => fake()->sentence(4),
            'body' => fake()->sentence(10),
            'data' => null,
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }

    public function pointsEarned(int $points = 10): static
    {
        return $this->state(fn () => [
            'type' => NotificationType::PointsEarned,
            'title' => 'Points Earned!',
            'body' => "You earned {$points} points for recycling.",
            'data' => ['points' => $points],
        ]);
    }

    public function streakMilestone(int $streak = 7): static
    {
        return $this->state(fn () => [
            'type' => NotificationType::StreakMilestone,
            'title' => "🔥 {$streak}-Day Streak!",
            'body' => "You've recycled for {$streak} days in a row. Keep it up!",
            'data' => ['streak' => $streak],
        ]);
    }

    public function welcome(): static
    {
        return $this->state(fn () => [
            'type' => NotificationType::Welcome,
            'title' => 'Welcome to Mobius!',
            'body' => 'Start recycling to earn points and rewards.',
        ]);
    }
}
