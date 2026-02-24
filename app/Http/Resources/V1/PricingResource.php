<?php

namespace App\Http\Resources\V1;

use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'attributes' => [
                'amount' => $this->amount instanceof Money ? $this->amount->getAmount()->__toString() : (string) $this->amount,
                'amountCurrency' => $this->currency,
                'minArea' => $this->min_area,
                'maxArea' => $this->max_area,
            ],
        ];
    }
}
