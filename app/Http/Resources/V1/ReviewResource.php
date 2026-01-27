<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'review',
            'id' => (string) $this->id,
            'attributes' => [
                'rating' => $this->rating,
                'comment' => $this->comment,
                'createdAt' => $this->created_at,
            ],
            'includes' => (object) [
                'cleaner' => CleanerResource::make($this->reviewable),
                'client' => ClientResource::make($this->user->client),
            ],
        ];
    }
}
