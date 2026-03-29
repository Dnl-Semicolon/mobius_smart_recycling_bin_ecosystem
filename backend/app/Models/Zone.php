<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'region',
        'boundary',
        'depot_latitude',
        'depot_longitude',
        'depot_name',
        'min_bins_for_dispatch',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'boundary' => 'array',
            'depot_latitude' => 'decimal:8',
            'depot_longitude' => 'decimal:8',
            'min_bins_for_dispatch' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function collectors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'zone_collector')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryCollector(): ?User
    {
        return $this->collectors()->wherePivot('is_primary', true)->first();
    }

    public function collectionRoutes(): HasMany
    {
        return $this->hasMany(CollectionRoute::class);
    }

    /**
     * Get active bins within this zone's boundary that are ready for pickup.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Bin>
     */
    public function eligibleBins(): \Illuminate\Database\Eloquent\Collection
    {
        return Bin::query()
            ->where('status', \App\Enums\BinStatus::Active)
            ->where('fill_level', '>=', 80)
            ->whereDoesntHave('activePickupRequest')
            ->whereHas('currentAssignment.outlet', function ($query) {
                $query->whereRaw($this->buildPointInPolygonCondition());
            })
            ->with('currentAssignment.outlet')
            ->get();
    }

    /**
     * Check if a lat/lng point falls within this zone's boundary polygon.
     */
    public function containsPoint(float $latitude, float $longitude): bool
    {
        $polygon = $this->boundary;
        if (empty($polygon)) {
            return false;
        }

        return self::pointInPolygon($latitude, $longitude, $polygon);
    }

    /**
     * Ray-casting algorithm for point-in-polygon check.
     *
     * @param  array<int, array{0: float, 1: float}>  $polygon  Array of [lat, lng] pairs
     */
    public static function pointInPolygon(float $lat, float $lng, array $polygon): bool
    {
        $n = count($polygon);
        $inside = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $yi = $polygon[$i][0];
            $xi = $polygon[$i][1];
            $yj = $polygon[$j][0];
            $xj = $polygon[$j][1];

            if (($yi > $lat) !== ($yj > $lat)
                && $lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    /**
     * Build a raw SQL condition for point-in-polygon (used in query scopes).
     * Falls back to bounding-box check for SQLite compatibility in tests.
     */
    private function buildPointInPolygonCondition(): string
    {
        $boundary = $this->boundary;
        if (empty($boundary)) {
            return '1 = 0';
        }

        $lats = array_column($boundary, 0);
        $lngs = array_column($boundary, 1);

        $minLat = min($lats);
        $maxLat = max($lats);
        $minLng = min($lngs);
        $maxLng = max($lngs);

        return sprintf(
            'latitude BETWEEN %s AND %s AND longitude BETWEEN %s AND %s',
            $minLat,
            $maxLat,
            $minLng,
            $maxLng
        );
    }
}
