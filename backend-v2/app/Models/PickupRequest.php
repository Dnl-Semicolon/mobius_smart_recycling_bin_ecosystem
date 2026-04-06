<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'bin_id', 'request_type', 'requested_by', 'reason', 'status',
    'assigned_to', 'assigned_at', 'completed_at',
])]
class PickupRequest extends Model
{
    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(Bin::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
