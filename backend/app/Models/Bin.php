<?php

namespace App\Models;

use App\Enums\BinStatus;
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
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fill_level' => 'integer',
            'status' => BinStatus::class,
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

    public function isReadyForPickup(): bool
    {
        return $this->fill_level >= 80;
    }
}
