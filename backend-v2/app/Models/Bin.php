<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'outlet_id', 'serial_number', 'api_token', 'status', 'fill_level',
    'weight_grams', 'capacity_liters', 'sensor_levels', 'latitude',
    'longitude', 'paired_at', 'last_pickup_at',
])]
class Bin extends Model
{
    protected function casts(): array
    {
        return [
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
}
