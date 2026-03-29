<?php

namespace App\Models;

use App\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

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
        'brand_id',
        'photo_path',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'contract_status' => ContractStatus::class,
        ];
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    /**
     * Parse operating_hours as structured JSON if possible.
     * Returns null for plain text strings (backward compatible).
     *
     * @return array<string, array{open: bool, from: string, to: string}>|null
     */
    public function getStructuredHours(): ?array
    {
        if (! $this->operating_hours) {
            return null;
        }

        $decoded = json_decode($this->operating_hours, true);

        return is_array($decoded) ? $decoded : null;
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'outlet_user')
            ->withPivot('role')
            ->withTimestamps();
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
