<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'notification',
            'id' => (string) $this->id,
            'attributes' => [
                'title' => $this->data['title'],
                'message' => $this->data['message'],
                'data' => (object) ($this->data['data'] ?? []),
                'readAt' => $this->read_at,
                'isRead' => isset($this->read_at),
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
        ];
    }
}
