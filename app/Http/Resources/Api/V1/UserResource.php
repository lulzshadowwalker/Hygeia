<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'user',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'email' => $this->when($request->user()?->id === $this->id, $this->email),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ]
        ];
    }
}
