<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecyclingTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\RecyclingTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'detection_event_id',
        'points_earned',
        'type',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'points_earned' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detectionEvent(): BelongsTo
    {
        return $this->belongsTo(DetectionEvent::class);
    }
}
