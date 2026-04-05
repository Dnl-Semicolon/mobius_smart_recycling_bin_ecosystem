<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'voucher_template_id', 'outlet_id', 'quota',
    'claimed_count', 'valid_from', 'valid_until',
])]
class VoucherAllocation extends Model
{
    protected function casts(): array
    {
        return [
            'quota' => 'integer',
            'claimed_count' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
        ];
    }

    public function voucherTemplate(): BelongsTo
    {
        return $this->belongsTo(VoucherTemplate::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(VoucherClaim::class);
    }

    public function hasQuotaRemaining(): bool
    {
        return $this->claimed_count < $this->quota;
    }
}
