<?php

namespace App\Enums;

enum RedemptionStatus: string
{
    case Pending = 'pending';
    case Fulfilled = 'fulfilled';
    case Expired = 'expired';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
