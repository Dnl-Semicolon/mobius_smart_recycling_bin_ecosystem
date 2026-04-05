<?php

namespace App\Enums;

enum BinStatus: string
{
    case Unpaired = 'unpaired';
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Offline = 'offline';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
