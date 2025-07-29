<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'message',
            'id' => $this->id,
            'attributes' => [
                'content' => $this->content,
                'type' => $this->type,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'sender' => [
                    'data' => new UserResource($this->whenLoaded('user'))
                ],
                'chat_room' => [
                    'data' => [
                        'type' => 'chat_room',
                        'id' => $this->chat_room_id
                    ]
                ]
            ]
        ];
    }
}
