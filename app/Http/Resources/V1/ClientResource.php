<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'client',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->user->name,
                'phone' => $this->user->phone,
                'email' => $this->user->email,
                'avatar' => $this->user->avatar,
                'status' => $this->user->status->value,
            ],
        ];
    }
}
