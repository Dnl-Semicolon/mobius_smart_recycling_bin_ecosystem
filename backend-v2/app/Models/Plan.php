<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'description', 'price_monthly', 'price_yearly', 'features', 'stripe_price_id', 'is_active'])]
class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
