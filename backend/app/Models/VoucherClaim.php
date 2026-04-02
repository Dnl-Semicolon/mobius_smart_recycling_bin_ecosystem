<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_template_id',
        'voucher_allocation_id',
        'user_id',
        'points_spent',
        'status',
        'claimed_at',
        'expires_at',
        'redeemed_at',
    ];

    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'claimed_at' => 'datetime',
            'expires_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    public function voucherTemplate(): BelongsTo
    {
        return $this->belongsTo(VoucherTemplate::class);
    }

    public function voucherAllocation(): BelongsTo
    {
        return $this->belongsTo(VoucherAllocation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRedeemed(): bool
    {
        return $this->status === 'redeemed';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || now()->isAfter($this->expires_at);
    }
}
