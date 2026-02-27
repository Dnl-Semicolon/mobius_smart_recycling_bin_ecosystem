<?php

namespace App\Enums;

enum PickupStatus: string
{
    case Pending = 'pending';
    case Claimed = 'claimed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
