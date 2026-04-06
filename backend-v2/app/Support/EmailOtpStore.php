<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class EmailOtpStore
{
    public const TTL_MINUTES = 10;

    public static function userKey(int $userId): string
    {
        return "register_email_otp:{$userId}";
    }

    public static function leadKey(string $token): string
    {
        return "lead_email_otp:{$token}";
    }

    public static function issue(string $key): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($key, Hash::make($code), now()->addMinutes(self::TTL_MINUTES));

        return $code;
    }

    public static function verify(string $key, string $code): bool
    {
        $hash = Cache::get($key);

        return is_string($hash) && Hash::check($code, $hash);
    }

    public static function forget(string $key): void
    {
        Cache::forget($key);
    }
}
