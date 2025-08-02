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
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'avatar' => $this->avatar,
                'status' => $this->status->value,
            ],
        ];
    }
}
