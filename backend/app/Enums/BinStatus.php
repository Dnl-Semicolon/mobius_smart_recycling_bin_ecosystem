<?php

namespace App\Enums;

enum BinStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
