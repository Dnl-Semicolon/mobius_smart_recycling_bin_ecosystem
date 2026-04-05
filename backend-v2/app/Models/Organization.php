<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'type', 'description', 'logo_path', 'website', 'is_active'])]
class Organization extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    /**
     * Get effective limit for a resource. Returns null if unlimited.
     */
    public function getEffectiveLimit(string $key): ?int
    {
        $sub = $this->subscription;
        if (! $sub) {
            return 0;
        }

        $override = $sub->{"custom_{$key}"};
        if ($override !== null) {
            return $override;
        }

        return $sub->plan?->{$key};
    }

    /**
     * Get current usage count for a resource.
     */
    public function getCurrentCount(string $key): int
    {
        $brandIds = $this->brands()->pluck('id');

        return match ($key) {
            'bin_limit' => DB::table('bins')
                ->whereIn('outlet_id', DB::table('outlets')->whereIn('brand_id', $brandIds)->pluck('id'))
                ->count(),
            'outlet_limit' => DB::table('outlets')->whereIn('brand_id', $brandIds)->count(),
            'staff_limit' => $this->users()->count(),
            default => 0,
        };
    }

    /**
     * Check if the org has reached its limit for a resource.
     */
    public function hasReachedLimit(string $key): bool
    {
        $limit = $this->getEffectiveLimit($key);
        if ($limit === null) {
            return false;
        }

        return $this->getCurrentCount($key) >= $limit;
    }

    /**
     * Get limit info for display: { current, max, reached }
     */
    public function getLimitInfo(string $key): array
    {
        $limit = $this->getEffectiveLimit($key);
        $current = $this->getCurrentCount($key);

        return [
            'current' => $current,
            'max' => $limit,
            'reached' => $limit !== null && $current >= $limit,
            'unlimited' => $limit === null,
        ];
    }
}
