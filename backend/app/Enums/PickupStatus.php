<?php

namespace App\Enums;

enum PickupStatus: string
{
    case Pending = 'pending';
    case Claimed = 'claimed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
