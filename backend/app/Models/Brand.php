<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'logo_path',
        'description',
        'website',
        'point_multiplier',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'point_multiplier' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Brand $brand) {
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

    public function voucherTemplates(): HasMany
    {
        return $this->hasMany(VoucherTemplate::class);
    }

    public function detectionEvents(): HasMany
    {
        return $this->hasMany(DetectionEvent::class, 'detected_brand_id');
    }

    /**
     * Compat: admin views check $brand->status.
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * Compat: admin views reference $brand->rewards.
     */
    public function rewards(): HasMany
    {
        return $this->voucherTemplates();
    }

    /**
     * Compat: admin views reference $brand->redemptions.
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherClaim::class);
    }

    /**
     * Compat: admin views reference $brand->adminUser.
     */
    public function getAdminUserAttribute(): ?User
    {
        return $this->organization?->users()->first();
    }
}
