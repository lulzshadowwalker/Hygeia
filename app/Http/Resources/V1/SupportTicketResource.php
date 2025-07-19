<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'support-ticket',
            'id' => (string) $this->id,
            'attributes' => [
                'number' => (string) $this->number,
                'subject' => $this->subject,
                'message' => $this->message,
                'status' => $this->status,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
