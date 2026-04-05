<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'brand_id', 'name', 'description', 'type', 'value',
    'points_required', 'valid_from', 'valid_until', 'is_active',
])]
class VoucherTemplate extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'points_required' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(VoucherAllocation::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(VoucherClaim::class);
    }

    public function isCurrentlyValid(): bool
    {
        return $this->is_active
            && $this->valid_from <= now()
            && $this->valid_until >= now();
    }
}
