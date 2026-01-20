<?php

namespace App\Http\Resources\Example;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birthday' => $this->birthday?->toDateString(),
            'phone' => $this->phone,
            'addresses' => $this->whenLoaded('addresses', function (): mixed {
                return AddressResource::collection($this->addresses);
            }),
        ];
    }
}
