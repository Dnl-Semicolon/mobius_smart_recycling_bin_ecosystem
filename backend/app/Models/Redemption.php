<?php

namespace App\Models;

use App\Enums\RedemptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    /** @use HasFactory<\Database\Factories\RedemptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'points_spent',
        'voucher_code',
        'status',
        'redeemed_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'status' => RedemptionStatus::class,
            'redeemed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }
}
