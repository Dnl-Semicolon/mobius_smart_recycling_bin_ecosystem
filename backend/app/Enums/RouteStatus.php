<?php

namespace App\Enums;

enum RouteStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
