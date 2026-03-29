<?php

namespace App\Models;

use App\Enums\RouteStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'collector_id',
        'status',
        'stops',
        'total_distance_km',
        'total_duration_min',
        'estimated_start_time',
        'started_at',
        'completed_at',
        'vroom_request',
        'vroom_response',
        'route_geometry',
    ];

    protected function casts(): array
    {
        return [
            'status' => RouteStatus::class,
            'stops' => 'array',
            'total_distance_km' => 'decimal:2',
            'total_duration_min' => 'integer',
            'estimated_start_time' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'vroom_request' => 'array',
            'vroom_response' => 'array',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', RouteStatus::Pending);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [RouteStatus::Pending, RouteStatus::Accepted, RouteStatus::Active]);
    }

    public function isPending(): bool
    {
        return $this->status === RouteStatus::Pending;
    }

    public function isAccepted(): bool
    {
        return $this->status === RouteStatus::Accepted;
    }

    public function isActive(): bool
    {
        return $this->status === RouteStatus::Active;
    }

    public function isCompleted(): bool
    {
        return $this->status === RouteStatus::Completed;
    }

    public function accept(): bool
    {
        if (! $this->isPending()) {
            return false;
        }

        $this->update(['status' => RouteStatus::Accepted]);

        return true;
    }

    public function start(): bool
    {
        if (! $this->isAccepted()) {
            return false;
        }

        $this->update([
            'status' => RouteStatus::Active,
            'started_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark a stop as completed, with proximity validation.
     *
     * @param  float|null  $collectorLat  Collector's current latitude
     * @param  float|null  $collectorLng  Collector's current longitude
     * @return array{success: bool, message: string, distance_meters?: float}
     */
    public function completeStop(int $order, ?float $collectorLat = null, ?float $collectorLng = null): array
    {
        $stops = $this->stops;
        $index = null;

        foreach ($stops as $i => $stop) {
            if ($stop['order'] === $order) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return ['success' => false, 'message' => 'Stop not found.'];
        }

        if (($stops[$index]['status'] ?? 'pending') === 'completed') {
            return ['success' => false, 'message' => 'Stop already completed.'];
        }

        // Proximity validation — collector must be near the stop
        $distanceMeters = null;
        if ($collectorLat !== null && $collectorLng !== null) {
            $stopLat = (float) $stops[$index]['latitude'];
            $stopLng = (float) $stops[$index]['longitude'];
            $distanceMeters = self::haversineMeters($collectorLat, $collectorLng, $stopLat, $stopLng);

            $threshold = config('services.route.proximity_threshold_meters', 200);
            if ($distanceMeters > $threshold) {
                return [
                    'success' => false,
                    'message' => "You must be within {$threshold}m of the stop to complete it. You are ".round($distanceMeters).'m away.',
                    'distance_meters' => round($distanceMeters, 1),
                ];
            }
        }

        $stops[$index]['status'] = 'completed';
        $stops[$index]['completed_at'] = now()->toIso8601String();

        // GPS audit trail
        if ($collectorLat !== null && $collectorLng !== null) {
            $stops[$index]['completed_latitude'] = $collectorLat;
            $stops[$index]['completed_longitude'] = $collectorLng;
            $stops[$index]['distance_from_stop_m'] = $distanceMeters !== null ? round($distanceMeters, 1) : null;
        }

        $this->update(['stops' => $stops]);

        // Complete the associated pickup request and reset bin
        if (isset($stops[$index]['bin_id'])) {
            $bin = Bin::find($stops[$index]['bin_id']);
            if ($bin) {
                $bin->update(['fill_level' => 0]);
            }

            if (isset($stops[$index]['pickup_request_id'])) {
                $pickup = PickupRequest::find($stops[$index]['pickup_request_id']);
                if ($pickup) {
                    $pickup->completeByCollector($this->collector_id);
                }
            }
        }

        return ['success' => true, 'message' => 'Stop completed.', 'distance_meters' => $distanceMeters !== null ? round($distanceMeters, 1) : null];
    }

    /**
     * Haversine distance between two coordinates in meters.
     */
    public static function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Skip a stop with a reason.
     *
     * @return array{success: bool, message: string}
     */
    public function skipStop(int $order, string $reason): array
    {
        $stops = $this->stops;
        $index = null;

        foreach ($stops as $i => $stop) {
            if ($stop['order'] === $order) {
                $index = $i;
                break;
            }
        }

        if ($index === null) {
            return ['success' => false, 'message' => 'Stop not found.'];
        }

        $stops[$index]['status'] = 'skipped';
        $stops[$index]['skip_reason'] = $reason;

        $this->update(['stops' => $stops]);

        // Cancel the pickup request so the bin goes back to the queue
        if (isset($stops[$index]['pickup_request_id'])) {
            $pickup = PickupRequest::find($stops[$index]['pickup_request_id']);
            if ($pickup) {
                $pickup->cancel();
            }
        }

        return ['success' => true, 'message' => 'Stop skipped.'];
    }

    public function complete(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        $this->update([
            'status' => RouteStatus::Completed,
            'completed_at' => now(),
        ]);

        return true;
    }

    public function reject(): bool
    {
        if (! $this->isPending()) {
            return false;
        }

        $this->update(['status' => RouteStatus::Cancelled]);

        // Cancel all associated pickup requests
        $stops = $this->stops ?? [];
        $pickupIds = array_filter(array_column($stops, 'pickup_request_id'));
        if (! empty($pickupIds)) {
            PickupRequest::whereIn('id', $pickupIds)->update([
                'status' => \App\Enums\PickupStatus::Cancelled,
            ]);
        }

        return true;
    }

    /**
     * Get the count of completed stops.
     */
    public function completedStopsCount(): int
    {
        return collect($this->stops ?? [])->where('status', 'completed')->count();
    }

    /**
     * Get the total number of stops.
     */
    public function totalStopsCount(): int
    {
        return count($this->stops ?? []);
    }

    /**
     * Check if all stops are done (completed or skipped).
     */
    public function allStopsDone(): bool
    {
        return collect($this->stops ?? [])->every(fn ($stop) => in_array($stop['status'] ?? 'pending', ['completed', 'skipped']));
    }
}
