<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class CollectorAgency extends Model
{
    /** @use HasFactory<\Database\Factories\CollectorAgencyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'contact_person',
        'email',
        'phone',
        'logo_path',
        'description',
        'fleet_size',
        'coverage_area',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'fleet_size' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CollectorAgency $agency) {
            if (empty($agency->slug)) {
                $agency->slug = Str::slug($agency->name);
            }
        });
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function collectors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'agency_collector')
            ->withPivot('status', 'invited_at', 'joined_at')
            ->withTimestamps();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
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
