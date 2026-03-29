<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    /** @use HasFactory<\Database\Factories\RewardFactory> */
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'name',
        'description',
        'points_cost',
        'stock',
        'image_path',
        'active',
        'sort_order',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points_cost' => 'integer',
            'stock' => 'integer',
            'active' => 'boolean',
            'sort_order' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('active', true)
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            });
    }

    public function isAvailable(): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->stock !== null && $this->stock <= 0) {
            return false;
        }

        return true;
    }
}
