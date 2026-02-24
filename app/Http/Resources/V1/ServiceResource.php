<?php

namespace App\Http\Resources\V1;

use Brick\Money\Money;
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
                'pricingModel' => $this->effectivePricingModel()->value,
                'pricePerMeter' => $this->price_per_meter instanceof Money ? $this->price_per_meter->getAmount()->__toString() : (string) $this->price_per_meter,
                'minArea' => $this->min_area,
                'currency' => $this->currency,
            ],
            'includes' => [
                'pricings' => $this->whenLoaded('pricings', function () {
                    return PricingResource::collection($this->pricings);
                }),
            ],
        ];
    }
}
