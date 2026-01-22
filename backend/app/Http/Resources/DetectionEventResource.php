<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetectionEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bin_id' => $this->bin_id,
            'waste_type' => $this->waste_type?->value,
            'confidence' => $this->confidence,
            'image_path' => $this->image_path,
            'detected_at' => $this->detected_at?->toIso8601String(),
            'bin' => $this->whenLoaded('bin', function () {
                return new BinResource($this->bin);
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
