<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionRouteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'zone' => $this->whenLoaded('zone', fn () => new ZoneResource($this->zone)),
            'stops' => $this->stops,
            'stops_completed' => $this->completedStopsCount(),
            'stops_total' => $this->totalStopsCount(),
            'route_geometry' => $this->route_geometry,
            'total_distance_km' => $this->total_distance_km !== null ? (float) $this->total_distance_km : null,
            'total_duration_min' => $this->total_duration_min !== null ? (int) $this->total_duration_min : null,
            'proximity_threshold_meters' => config('services.route.proximity_threshold_meters', 200),
            'estimated_start_time' => $this->estimated_start_time?->toIso8601String(),
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
