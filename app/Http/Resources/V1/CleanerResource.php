<?php

namespace App\Http\Resources\V1;

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
        //  TODO: Implement cleaner resource with real data
        return [
            'type' => 'cleaner',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => 'John Doe',
                'phone' => '+962791234567',
                'email' => 'email@example.com',
                'avatar' => "https://ui-avatars.com/api/?name=John+Doe",
                'status' => 'active',
                'availableDays' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                'maxHoursPerWeek' => 40,
                'timeSlots' => ['morning', 'afternoon', 'evening'],
                'yearsOfExperience' => 5,
                'hasCleaningSupplies' => true,
                'comfortableWithPets' => true,
                'serviceRadius' => 10,
                'agreedToTerms' => true,

                //  TODO: Implement isFavorite attribute based on the current authenticated user
                'isFavorite' => false,
            ],
            'includes' => [
                'previousServices' => ServiceResource::collection($this->previousServices ?? []),
                'preferredServices' => ServiceResource::collection($this->preferredServices ?? []),
                'serviceArea' => $this->serviceArea ? DistrictResource::make($this->serviceArea) : null,
            ],
        ];
    }
}
