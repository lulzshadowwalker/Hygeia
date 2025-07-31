<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'chat-room',
            'id' => (string) $this->id,
            'attributes' => [
                'type' => $this->type->value,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => (object) [
                'participants' => $this->whenLoaded(
                    'participants',
                    fn() =>
                    ParticipantResource::collection($this->participants)
                ),
                'latestMessage' =>
                $this->when(
                    $this->messages->isNotEmpty(),
                    fn() => new MessageResource($this->messages->first())
                )
            ]
        ];
    }
}
