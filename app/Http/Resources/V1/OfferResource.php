<?php

namespace App\Http\Resources\V1;

use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'offer',
            'id' => (string) $this->id,
            'attributes' => [
                'hasCleaningMaterials' => $this->has_cleaning_material,
                'urgency' => $this->urgency?->value,
                'scheduledAt' => optional(
                    $this->scheduled_at,
                )->toIso8601String(),
                'selectedAmount' => $this->selected_amount instanceof Money ? $this->selected_amount->getAmount()->__toString() : (string) $this->selected_amount,
                'area' => (int) $this->area,
                'pricePerMeter' => $this->price_per_meter instanceof Money ? $this->price_per_meter->getAmount()->__toString() : (string) $this->price_per_meter,
                'images' => $this->images,
                'amount' => $this->amount instanceof Money ? $this->amount->getAmount()->__toString() : (string) $this->amount,
                'discountAmount' => $this->calculateDiscountAmount(),
                'currency' => $this->currency,
                'location' => $this->location,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'createdAt' => optional($this->created_at)->toIso8601String(),
                'updatedAt' => optional($this->updated_at)->toIso8601String(),
            ],
            'includes' => [
                // 'client' => new ClientResource($this->client),
                'service' => new ServiceResource($this->service),
                'pricing' => new PricingResource($this->pricing),
                'extras' => ExtraResource::collection($this->extras),
                'promocode' => $this->whenLoaded('promocode', fn () => new PromocodeResource($this->promocode)),
            ],
        ];
    }

    protected function calculateDiscountAmount(): string
    {
        if (! $this->selected_amount instanceof Money || ! $this->amount instanceof Money) {
            return '0.00';
        }

        $extrasTotal = $this->extras->reduce(
            fn (Money $carry, $extra) => $carry->plus($extra->pivot->amount instanceof Money ? $extra->pivot->amount : Money::of('0', $this->currency ?? 'HUF')),
            Money::of('0', $this->currency ?? 'HUF')
        );

        $beforeDiscount = $this->selected_amount->plus($extrasTotal);
        $discount = $beforeDiscount->minus($this->amount);

        if ($discount->isNegative()) {
            return '0.00';
        }

        return $discount->getAmount()->toScale(2)->__toString();
    }
}
