<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RouteStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'collection_route_id',
        'bin_id',
        'pickup_request_id',
        'stop_order',
        'address',
        'distance_km',
        'duration_min',
        'status',
        'eta',
        'completed_at',
        'completed_latitude',
        'completed_longitude',
        'proof_image_path',
        'skip_reason',
    ];

    protected function casts(): array
    {
        return [
            'stop_order' => 'integer',
            'distance_km' => 'decimal:2',
            'duration_min' => 'integer',
            'eta' => 'datetime',
            'completed_at' => 'datetime',
            'completed_latitude' => 'decimal:7',
            'completed_longitude' => 'decimal:7',
        ];
    }

    public function collectionRoute(): BelongsTo
    {
        return $this->belongsTo(CollectionRoute::class);
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function pickupRequest(): BelongsTo
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isSkipped(): bool
    {
        return $this->status === 'skipped';
    }
}
