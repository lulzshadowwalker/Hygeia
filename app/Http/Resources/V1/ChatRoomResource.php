<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'chat-room',
            'id' => (string) $this->id,
            'attributes' => [
                'name' => $this->name,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                // TODO: We might want to either make the participants return a collection of both cleaners and clients
                // and on the frontend we can rely on the type of the resource.
                'participants' => UserResource::collection($this->whenLoaded('participants')),
                'latestMessage' => MessageResource::make($this->whenLoaded('latestMessage')),
            ],
        ];
    }
}
