<?php

namespace App\Http\Resources\V1;

use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromocodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'promocode',
            'id' => (string) $this->id,
            'attributes' => [
                'code' => $this->code,
                'discountPercentage' => (string) $this->discount_percentage,
                'maxDiscountAmount' => $this->max_discount_amount instanceof Money ? $this->max_discount_amount->getAmount()->__toString() : (string) $this->max_discount_amount,
                'currency' => $this->currency,
            ],
        ];
    }
}
