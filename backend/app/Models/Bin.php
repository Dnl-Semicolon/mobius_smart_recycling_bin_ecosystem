<?php

namespace App\Models;

use App\Enums\BinStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bin extends Model
{
    use HasFactory;

    protected $fillable = [
        'outlet_id',
        'serial_number',
        'api_token',
        'status',
        'fill_level',
        'weight_grams',
        'capacity_liters',
        'sensor_levels',
        'latitude',
        'longitude',
        'paired_at',
        'last_pickup_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => BinStatus::class,
            'fill_level' => 'integer',
            'weight_grams' => 'integer',
            'capacity_liters' => 'decimal:2',
            'sensor_levels' => 'array',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'paired_at' => 'datetime',
            'last_pickup_at' => 'datetime',
        ];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function binSessions(): HasMany
    {
        return $this->hasMany(BinSession::class);
    }

    public function pickupRequests(): HasMany
    {
        return $this->hasMany(PickupRequest::class);
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class);
    }

    public function isReadyForPickup(): bool
    {
        return $this->fill_level >= 75;
    }

    /**
     * Compat accessor: old views reference $bin->currentAssignment->outlet.
     * New schema has outlet_id directly on bins.
     */
    public function getCurrentAssignmentAttribute(): ?object
    {
        if (! $this->outlet_id) {
            return null;
        }

        return (object) [
            'outlet' => $this->outlet,
            'assigned_at' => $this->paired_at ?? $this->created_at,
            'unassigned_at' => null,
        ];
    }

    /**
     * Compat: admin views reference $bin->activePickupRequest.
     */
    public function activePickupRequest(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PickupRequest::class)->where('status', 'pending');
    }

    /**
     * Compat: admin views reference $bin->assignments (history).
     * New schema has no assignment table — return empty.
     */
    public function getAssignmentsAttribute(): \Illuminate\Support\Collection
    {
        if (! $this->outlet_id) {
            return collect();
        }

        return collect([(object) [
            'outlet' => $this->outlet,
            'assigned_at' => $this->paired_at ?? $this->created_at,
            'unassigned_at' => null,
        ]]);
    }

    /**
     * Get detection events through bin sessions.
     */
    public function detectionEvents(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(DetectionEvent::class, BinSession::class);
    }
}
