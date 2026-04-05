<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'description', 'price_monthly', 'price_yearly',
    'bin_limit', 'outlet_limit', 'staff_limit', 'api_access',
    'features', 'stripe_price_id', 'stripe_price_yearly_id', 'is_active',
])]
class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'bin_limit' => 'integer',
            'outlet_limit' => 'integer',
            'staff_limit' => 'integer',
            'api_access' => 'boolean',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
