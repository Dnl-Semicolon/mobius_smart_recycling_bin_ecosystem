<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrandApplication extends Model
{
    protected $fillable = [
        'brand_id',
        'user_id',
        'status',
        'brand_name',
        'description',
        'website_url',
        'logo_path',
        'contact_person',
        'contact_email',
        'contact_phone',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isClaimingExisting(): bool
    {
        return $this->brand_id !== null;
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
}
