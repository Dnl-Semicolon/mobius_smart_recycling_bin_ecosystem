<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Outlet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'contact_name',
        'contact_phone',
        'contact_email',
        'operating_hours',
        'contract_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'contract_status' => ContractStatus::class,
        ];
    }

    public function binAssignments(): HasMany
    {
        return $this->hasMany(BinAssignment::class);
    }

    public function currentBinAssignments(): HasMany
    {
        return $this->hasMany(BinAssignment::class)->whereNull('unassigned_at');
    }

    public function bins(): HasManyThrough
    {
        return $this->hasManyThrough(
            Bin::class,
            BinAssignment::class,
            'outlet_id',
            'id',
            'id',
            'bin_id'
        )->whereNull('bin_assignments.unassigned_at');
    }
}
