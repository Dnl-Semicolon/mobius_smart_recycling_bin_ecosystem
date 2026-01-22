<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BinResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'serial_number' => $this->serial_number,
            'fill_level' => $this->fill_level,
            'status' => $this->status?->value,
            'is_ready_for_pickup' => $this->isReadyForPickup(),
            'current_assignment' => $this->whenLoaded('currentAssignment', function () {
                return new BinAssignmentResource($this->currentAssignment);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
