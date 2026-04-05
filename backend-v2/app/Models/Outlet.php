<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'brand_id', 'name', 'address', 'street', 'city', 'state', 'postcode', 'country', 'latitude', 'longitude', 'is_active'])]
class Outlet extends Model
{
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function storeOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class);
    }
}
