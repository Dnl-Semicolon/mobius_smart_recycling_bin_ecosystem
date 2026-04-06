<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function normalize(?string $value): ?string
    {
        $local = self::toLocalDigits($value);

        if ($local === null || ! self::isValidLocalMobile($local)) {
            return null;
        }

        return '+60'.substr($local, 1);
    }

    public static function isValid(?string $value): bool
    {
        return self::normalize($value) !== null;
    }

    public static function formatForDisplay(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = self::normalize($value);

        if ($normalized === null) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        $local = '0'.substr($normalized, 3);

        if (str_starts_with($local, '011')) {
            return sprintf(
                '%s-%s %s',
                substr($local, 0, 3),
                substr($local, 3, 4),
                substr($local, 7, 4),
            );
        }

        return sprintf(
            '%s-%s %s',
            substr($local, 0, 3),
            substr($local, 3, 3),
            substr($local, 6, 4),
        );
    }

    private static function toLocalDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed);

        if ($digits === null || $digits === '') {
            return null;
        }

        if (str_starts_with($digits, '60')) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }

    private static function isValidLocalMobile(string $local): bool
    {
        if (! str_starts_with($local, '01')) {
            return false;
        }

        if (str_starts_with($local, '011')) {
            return preg_match('/^011\d{8}$/', $local) === 1;
        }

        return preg_match('/^01\d{8}$/', $local) === 1;
    }
}
