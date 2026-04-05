<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
