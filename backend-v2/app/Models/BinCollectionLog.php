<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bin_id', 'collector_id', 'route_stop_id', 'pickup_request_id',
    'fill_level_before', 'collected_latitude', 'collected_longitude',
])]
class BinCollectionLog extends Model
{
    protected function casts(): array
    {
        return [
            'fill_level_before' => 'integer',
            'collected_latitude' => 'decimal:7',
            'collected_longitude' => 'decimal:7',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function routeStop(): BelongsTo
    {
        return $this->belongsTo(RouteStop::class);
    }
}
