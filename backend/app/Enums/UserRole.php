<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Collector = 'collector';
    case PublicUser = 'public_user';
}
