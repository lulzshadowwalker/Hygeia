<?php

namespace App\Http\Resources\V1;

use Brick\Money\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'booking',
            'id' => (string) $this->id,
            'attributes' => [
                'hasCleaningMaterials' => $this->has_cleaning_material,
                'urgency' => $this->urgency?->value,
                'scheduledAt' => optional(
                    $this->scheduled_at,
                )->toIso8601String(),
                'selectedAmount' => $this->selected_amount instanceof Money ? $this->selected_amount->getAmount()->__toString() : (string) $this->selected_amount,
                'selectedAmountCurrency' => $this->currency,
                'amount' => $this->amount instanceof Money ? $this->amount->getAmount()->__toString() : (string) $this->amount,
                'amountCurrency' => $this->currency,
                'area' => (int) $this->area,
                'pricePerMeter' => $this->price_per_meter instanceof Money ? $this->price_per_meter->getAmount()->__toString() : (string) $this->price_per_meter,
                'pricePerMeterCurrency' => $this->currency,
                'status' => $this->status?->value,
                'location' => $this->location,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'images' => $this->images,
                'createdAt' => optional($this->created_at)->toIso8601String(),
                'updatedAt' => optional($this->updated_at)->toIso8601String(),
            ],
            'includes' => [
                'client' => new ClientResource($this->client),
                'cleaner' => new CleanerResource($this->cleaner),
                'service' => new ServiceResource($this->service),
                'pricing' => new PricingResource($this->pricing),
                'extras' => ExtraResource::collection($this->extras),
            ],
        ];
    }
}
