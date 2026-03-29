<?php

namespace App\Enums;

enum NotificationType: string
{
    case PointsEarned = 'points_earned';
    case StreakMilestone = 'streak_milestone';
    case Achievement = 'achievement';
    case PickupComplete = 'pickup_complete';
    case System = 'system';
    case Welcome = 'welcome';

    public function label(): string
    {
        return match ($this) {
            self::PointsEarned => 'Points Earned',
            self::StreakMilestone => 'Streak Milestone',
            self::Achievement => 'Achievement',
            self::PickupComplete => 'Pickup Complete',
            self::System => 'System',
            self::Welcome => 'Welcome',
        };
    }
}
