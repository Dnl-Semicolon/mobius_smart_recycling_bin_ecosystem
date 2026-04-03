<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case BrandOwner = 'brand_owner';
    case StoreOwner = 'store_owner';
    case Collector = 'collector';
    case PublicUser = 'public_user';
    case AgencyAdmin = 'agency_admin';

    public function label(): string
    {
        return ucwords(str_replace('_', ' ', $this->value));
    }
}
