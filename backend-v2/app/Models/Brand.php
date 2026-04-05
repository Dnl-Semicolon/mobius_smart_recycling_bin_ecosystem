<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['organization_id', 'name', 'slug', 'logo_path', 'description', 'website', 'point_multiplier', 'is_active'])]
class Brand extends Model
{
    protected function casts(): array
    {
        return [
            'point_multiplier' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Brand $brand): void {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }
}
