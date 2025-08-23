<?php

namespace App\Http\Resources\V1;

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
                'scheduledAt' => optional($this->scheduled_at)->toIso8601String(),
                'selectedAmount' => $this->selected_amount,
                'amount' => $this->amount,
                'status' => $this->status?->value,
                'createdAt' => optional($this->created_at)->toIso8601String(),
                'updatedAt' => optional($this->updated_at)->toIso8601String(),
            ],
            'includes' => [
                'client' => new ClientResource($this->client->user),
                'service' => new ServiceResource($this->service),
                'pricing' => new PricingResource($this->pricing),
                'extras' => ExtraResource::collection($this->extras),
            ],
        ];
    }
}
