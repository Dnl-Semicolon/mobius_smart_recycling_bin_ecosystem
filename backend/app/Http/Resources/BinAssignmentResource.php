<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BinAssignmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bin_id' => $this->bin_id,
            'outlet_id' => $this->outlet_id,
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'unassigned_at' => $this->unassigned_at?->toIso8601String(),
            'is_current' => $this->isCurrent(),
            'outlet' => $this->whenLoaded('outlet', function () {
                return new OutletResource($this->outlet);
            }),
        ];
    }
}
