<?php

namespace App\Http\Resources\V1;

use App\Models\District;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CleanerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->user;
        $serviceArea = $this->serviceArea ?? ($this->service_area_id ? District::find($this->service_area_id) : null);

        $isFavorite = false;
        $authenticatedUser = $request->user();

        if ($authenticatedUser?->isClient) {
            $client = $authenticatedUser->client;

            if ($client) {
                $isFavorite = $client->favoriteCleaners()
                    ->whereKey($this->id)
                    ->exists();
            }
        }

        return [
            'type' => 'cleaner',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $user?->name,
                'phone' => $user?->phone,
                'email' => $user?->email,
                'avatar' => $user?->avatar,
                'status' => $user?->status?->value,
                'availableDays' => $this->available_days ?? [],
                'maxHoursPerWeek' => $this->max_hours_per_week,
                'timeSlots' => $this->time_slots ?? [],
                'yearsOfExperience' => $this->years_of_experience,
                'hasCleaningSupplies' => (bool) $this->has_cleaning_supplies,
                'comfortableWithPets' => (bool) $this->comfortable_with_pets,
                'serviceRadius' => $this->service_radius,
                'agreedToTerms' => (bool) $this->agreed_to_terms,
                'isFavorite' => $isFavorite,
            ],
            'includes' => [
                'previousServices' => ServiceResource::collection($this->previousServices ?? []),
                'preferredServices' => ServiceResource::collection($this->preferredServices ?? []),
                'serviceArea' => $serviceArea ? DistrictResource::make($serviceArea) : null,
            ],
        ];
    }
}
