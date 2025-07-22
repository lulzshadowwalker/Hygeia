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
        //  TODO: Implement client resource with real data
        return [
            'type' => 'client',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => 'John Doe',
                'phone' => '+962791234567',
                'email' => 'email@example.com',
                'avatar' => "https://ui-avatars.com/api/?name=John+Doe",
                'status' => 'active',
            ],
        ];
    }
}
