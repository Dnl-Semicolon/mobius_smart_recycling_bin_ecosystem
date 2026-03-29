<?php

namespace App\Models;

use App\Enums\BinStatus;
use App\Enums\PickupStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bin extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'serial_number',
        'fill_level',
        'compartments',
        'status',
        'last_seen_at',
        'ip_address',
    ];

    protected $attributes = [
        'fill_level' => 0,
        'status' => 'active',
    ];

    protected static function booted(): void
    {
        static::creating(function (Bin $bin): void {
            if (empty($bin->serial_number)) {
                $bin->serial_number = static::generateSerialNumber();
            }
        });
    }

    public static function generateSerialNumber(): string
    {
        $year = date('Y');
        $prefix = "MBR-{$year}-";

        $maxSerial = static::withTrashed()
            ->where('serial_number', 'like', "{$prefix}%")
            ->max('serial_number');

        $nextNumber = $maxSerial
            ? (int) substr($maxSerial, -3) + 1
            : 1;

        return $prefix.str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected function casts(): array
    {
        return [
            'fill_level' => 'integer',
            'compartments' => 'array',
            'status' => BinStatus::class,
            'last_seen_at' => 'datetime',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BinAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(BinAssignment::class)->whereNull('unassigned_at');
    }

    public function detectionEvents(): HasMany
    {
        return $this->hasMany(DetectionEvent::class);
    }

    public function pickupRequests(): HasMany
    {
        return $this->hasMany(PickupRequest::class);
    }

    public function activePickupRequest(): HasOne
    {
        return $this->hasOne(PickupRequest::class)
            ->whereIn('status', [PickupStatus::Pending, PickupStatus::Claimed])
            ->latest();
    }

    public function isReadyForPickup(): bool
    {
        return $this->fill_level >= 80;
    }
}
