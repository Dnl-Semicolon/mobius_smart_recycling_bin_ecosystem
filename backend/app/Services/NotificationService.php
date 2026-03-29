<?php

namespace App\Services;

use App\Enums\NotificationType;
use App\Enums\WasteType;
use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    /** @var int[] */
    private const STREAK_MILESTONES = [7, 14, 30, 60, 100];

    public function notifyPointsEarned(User $user, int $points, WasteType $wasteType): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id,
            'type' => NotificationType::PointsEarned,
            'title' => 'Points Earned!',
            'body' => "You earned {$points} points for recycling {$wasteType->label()}.",
            'data' => ['points' => $points, 'waste_type' => $wasteType->value],
        ]);
    }

    public function notifyStreakMilestone(User $user, int $streak): ?AppNotification
    {
        if (! in_array($streak, self::STREAK_MILESTONES, true)) {
            return null;
        }

        // Prevent duplicate milestone notifications
        $exists = AppNotification::where('user_id', $user->id)
            ->where('type', NotificationType::StreakMilestone)
            ->whereJsonContains('data->streak', $streak)
            ->exists();

        if ($exists) {
            return null;
        }

        return AppNotification::create([
            'user_id' => $user->id,
            'type' => NotificationType::StreakMilestone,
            'title' => "{$streak}-Day Streak!",
            'body' => "You've recycled for {$streak} days in a row. Keep it up!",
            'data' => ['streak' => $streak],
        ]);
    }

    public function notifyWelcome(User $user): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id,
            'type' => NotificationType::Welcome,
            'title' => 'Welcome to Mobius!',
            'body' => 'Start recycling to earn points and rewards. Every item counts!',
        ]);
    }

    public function notifySystem(User $user, string $title, string $body): AppNotification
    {
        return AppNotification::create([
            'user_id' => $user->id,
            'type' => NotificationType::System,
            'title' => $title,
            'body' => $body,
        ]);
    }
}
