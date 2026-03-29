<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Collector = 'collector';
    case PublicUser = 'public_user';
    case StoreOwner = 'store_owner';
    case AgencyAdmin = 'agency_admin';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
