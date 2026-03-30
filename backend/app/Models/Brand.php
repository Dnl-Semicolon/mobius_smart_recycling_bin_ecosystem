<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Brand extends Model
{
    /** @use HasFactory<\Database\Factories\BrandFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'primary_color',
        'description',
        'points_multiplier',
        'rewards_budget',
        'active',
        'status',
        'contact_person',
        'contact_email',
        'contact_phone',
        'website_url',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'points_multiplier' => 'decimal:2',
            'rewards_budget' => 'integer',
            'active' => 'boolean',
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',
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

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function redemptions(): HasManyThrough
    {
        return $this->hasManyThrough(Redemption::class, Reward::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(BrandApplication::class);
    }

    public function scopePending(Builder $query): void
    {
        $query->where('status', ApplicationStatus::Pending);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('status', ApplicationStatus::Approved);
    }

    public function scopeRejected(Builder $query): void
    {
        $query->where('status', ApplicationStatus::Rejected);
    }

    /**
     * Deduct from this brand's rewards budget.
     */
    public function deductBudget(int $points): bool
    {
        if ($this->rewards_budget < $points) {
            return false;
        }

        $this->decrement('rewards_budget', $points);

        return true;
    }
}
