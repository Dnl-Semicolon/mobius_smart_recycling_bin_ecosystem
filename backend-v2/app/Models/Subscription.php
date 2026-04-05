<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'organization_id', 'plan_id', 'status', 'billing_interval',
    'custom_bin_limit', 'custom_outlet_limit', 'custom_staff_limit',
    'custom_price_monthly', 'notes', 'stripe_price_id',
    'starts_at', 'ends_at', 'renews_at',
])]
class Subscription extends Model
{
    protected $table = 'organization_subscriptions';

    protected function casts(): array
    {
        return [
            'custom_bin_limit' => 'integer',
            'custom_outlet_limit' => 'integer',
            'custom_staff_limit' => 'integer',
            'custom_price_monthly' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'renews_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
