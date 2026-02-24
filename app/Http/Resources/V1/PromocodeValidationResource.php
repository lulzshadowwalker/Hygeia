<?php

namespace App\Http\Resources\V1;

use App\Enums\PromocodeValidationReason;
use App\Services\Pricing\BookingPriceBreakdown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromocodeValidationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var BookingPriceBreakdown|null $priceBreakdown */
        $priceBreakdown = $this->resource['priceBreakdown'] ?? null;
        /** @var PromocodeValidationReason|null $reason */
        $reason = $this->resource['reason'] ?? null;

        return [
            'type' => 'promocode-validation',
            'id' => 'promocode-validation',
            'attributes' => [
                'valid' => (bool) ($this->resource['valid'] ?? false),
                'reason' => $reason?->value,
                'pricing' => $priceBreakdown ? [
                    'selectedAmount' => $priceBreakdown->selectedAmount->getAmount()->__toString(),
                    'extrasAmount' => $priceBreakdown->extrasAmount->getAmount()->__toString(),
                    'discountAmount' => $priceBreakdown->discountAmount->getAmount()->__toString(),
                    'totalAmount' => $priceBreakdown->totalAmount->getAmount()->__toString(),
                    'currency' => $priceBreakdown->currency,
                ] : null,
            ],
            'includes' => [
                'promocode' => isset($this->resource['promocode']) && $this->resource['promocode']
                    ? PromocodeResource::make($this->resource['promocode'])->toArray($request)
                    : null,
            ],
        ];
    }
}
