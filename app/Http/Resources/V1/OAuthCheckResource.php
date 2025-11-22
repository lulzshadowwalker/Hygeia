<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OAuthCheckResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'oauth-check',
            'id' => $this->provider.':'.$this->providerId,
            'attributes' => [
                'exists' => $this->exists,
                'provider' => $this->provider,
                'email' => $this->email,
                'name' => $this->name,
                'role' => $this->when($this->exists, $this->role),
                'userId' => $this->when($this->exists, $this->userId),
            ],
        ];
    }
}
