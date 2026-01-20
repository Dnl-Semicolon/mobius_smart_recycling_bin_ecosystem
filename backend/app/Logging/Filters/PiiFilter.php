<?php

namespace App\Logging\Filters;

class PiiFilter
{
    /**
     * @var array<int, string>
     */
    private array $blocklist;

    /**
     * @var array<int, string>
     */
    private array $hashFields;

    public function __construct()
    {
        $this->blocklist = config('wide-events.pii.blocklist', []);
        $this->hashFields = config('wide-events.pii.hash_fields', []);
    }

    /**
     * Filter an array, redacting blocklisted fields and hashing specified fields.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function filter(array $data): array
    {
        return $this->filterRecursive($data);
    }

    /**
     * Recursively filter array data.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function filterRecursive(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if ($this->isBlocked($normalizedKey)) {
                $filtered[$key] = '[REDACTED]';
            } elseif ($this->shouldHash($normalizedKey) && is_string($value)) {
                $filtered[$key] = 'sha256:'.substr(hash('sha256', $value), 0, 16);
            } elseif (is_array($value)) {
                $filtered[$key] = $this->filterRecursive($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Check if a key is in the blocklist.
     */
    private function isBlocked(string $key): bool
    {
        return in_array($key, array_map('strtolower', $this->blocklist), true);
    }

    /**
     * Check if a key should be hashed.
     */
    private function shouldHash(string $key): bool
    {
        return in_array($key, array_map('strtolower', $this->hashFields), true);
    }
}
