<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'collector_id', 'status', 'depot_latitude', 'depot_longitude', 'depot_name',
    'total_distance_km', 'total_duration_min', 'route_polyline', 'google_response',
    'started_at', 'completed_at',
])]
class CollectionRoute extends Model
{
    protected function casts(): array
    {
        return [
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

    public function stops(): HasMany
    {
        return $this->hasMany(RouteStop::class)->orderBy('stop_order');
    }
}
