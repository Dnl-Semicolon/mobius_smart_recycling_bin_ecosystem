<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'region' => $this->region,
            'depot_name' => $this->depot_name,
            'depot_latitude' => (float) $this->depot_latitude,
            'depot_longitude' => (float) $this->depot_longitude,
            'min_bins_for_dispatch' => $this->min_bins_for_dispatch,
            'is_active' => $this->is_active,
        ];
    }
}
