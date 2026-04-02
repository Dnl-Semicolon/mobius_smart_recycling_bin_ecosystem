<?php

namespace App\Models;

use App\Enums\RouteStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'collector_id',
        'status',
        'depot_latitude',
        'depot_longitude',
        'depot_name',
        'total_distance_km',
        'total_duration_min',
        'route_polyline',
        'google_response',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RouteStatus::class,
            'depot_latitude' => 'decimal:7',
            'depot_longitude' => 'decimal:7',
            'total_distance_km' => 'decimal:2',
            'total_duration_min' => 'integer',
            'google_response' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function routeStops(): HasMany
    {
        return $this->hasMany(RouteStop::class)->orderBy('stop_order');
    }

    public function isPending(): bool
    {
        return $this->status === RouteStatus::Pending;
    }

    public function isAccepted(): bool
    {
        return $this->status === RouteStatus::Accepted;
    }

    public function isInProgress(): bool
    {
        return $this->status === RouteStatus::InProgress;
    }

    public function isCompleted(): bool
    {
        return $this->status === RouteStatus::Completed;
    }
}
