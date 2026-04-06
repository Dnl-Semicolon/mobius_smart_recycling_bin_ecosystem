<?php

namespace App\Support;

use Illuminate\Support\Str;

class EmailNormalizer
{
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = Str::of($value)
            ->trim()
            ->lower()
            ->value();

        return $normalized === '' ? null : $normalized;
    }
}
