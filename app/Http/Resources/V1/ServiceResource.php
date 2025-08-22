<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'service',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'type' => $this->type->value,
            ],
            'includes' => [
                'pricings' => $this->whenLoaded('pricings', function () {
                    return PricingResource::collection($this->pricings);
                }),
            ],
        ];
    }
}
