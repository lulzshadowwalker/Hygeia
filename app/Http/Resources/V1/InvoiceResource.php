<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'invoice',
            'id' => (string) $this->id,
            'attributes' => [
                'file' => 'https://morth.nic.in/sites/default/files/dd12-13_0.pdf',
                'number' => $this->number,
                'createdAt' => $this->created_at,
            ],
        ];
    }
}
