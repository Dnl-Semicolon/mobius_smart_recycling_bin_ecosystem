<?php

namespace App\Enums;

enum ContractStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
