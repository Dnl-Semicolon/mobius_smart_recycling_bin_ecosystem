<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Outlet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class);
    }

    public function voucherAllocations(): HasMany
    {
        return $this->hasMany(VoucherAllocation::class);
    }

    /**
     * Compat: old views use currentBinAssignments to count bins at this outlet.
     */
    public function currentBinAssignments(): HasMany
    {
        return $this->bins();
    }

    /**
     * Compat: admin views reference $outlet->contract_status->value and ->label().
     */
    public function getContractStatusAttribute(): object
    {
        $value = $this->is_active ? 'active' : 'inactive';

        return new class($value)
        {
            public string $value;

            public function __construct(string $value)
            {
                $this->value = $value;
            }

            public function label(): string
            {
                return ucfirst($this->value);
            }
        };
    }

    /**
     * Compat: admin views reference $outlet->managers.
     */
    public function managers(): HasMany
    {
        return $this->hasMany(User::class, 'id', 'user_id');
    }
}
