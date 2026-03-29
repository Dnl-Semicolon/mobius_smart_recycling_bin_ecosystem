<?php

namespace App\Enums;

enum RouteStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
