<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExtraResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'extra',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'amount' => $this->amount,
            ],
        ];
    }
}
