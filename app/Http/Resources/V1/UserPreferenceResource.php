<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPreferenceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user-preference',
            'id' => (string) $this->id,
            'attributes' => [
                'language' => $this->language,
                'emailNotifications' => $this->email_notifications,
                'pushNotifications' => $this->push_notifications,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
