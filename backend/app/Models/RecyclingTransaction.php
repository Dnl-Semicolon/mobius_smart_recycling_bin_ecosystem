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
        'bin_session_id',
        'type',
        'points',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function binSession(): BelongsTo
    {
        return $this->belongsTo(BinSession::class);
    }
}
