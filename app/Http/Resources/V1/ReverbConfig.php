<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReverbConfig extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'reverb-config',
            'id' => 'reverb-config',
            'attributes' => [
                'key' => $this->key,
                'host' => $this->host,
                'port' => $this->port,
                'scheme' => $this->scheme,
            ],
        ];
    }
}
