<?php

namespace App\Enums;

enum PickupStatus: string
{
    case Pending = 'pending';
    case Claimed = 'assigned';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Claimed => 'Claimed',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }
}
