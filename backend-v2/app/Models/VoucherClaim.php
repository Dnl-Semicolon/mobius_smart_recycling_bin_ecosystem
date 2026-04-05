<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'voucher_template_id', 'voucher_allocation_id', 'user_id',
    'points_spent', 'status', 'qr_code',
    'claimed_at', 'expires_at', 'redeemed_at',
])]
class VoucherClaim extends Model
{
    protected function casts(): array
    {
        return [
            'points_spent' => 'integer',
            'claimed_at' => 'datetime',
            'expires_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VoucherClaim $claim): void {
            if (empty($claim->qr_code)) {
                $claim->qr_code = 'VCR-'.Str::upper(Str::random(12));
            }
        });
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
}
