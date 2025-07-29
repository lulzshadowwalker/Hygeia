<?php

namespace App\Http\Resources\Api\V1;

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
            'type' => 'chat_room',
            'id' => $this->id,
            'attributes' => [
                'name' => $this->name,
                'description' => $this->description,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            'relationships' => [
                'participants' => [
                    'data' => UserResource::collection($this->whenLoaded('participants'))
                ],
                'latest_message' => [
                    'data' => $this->when(
                        $this->relationLoaded('messages') && $this->messages->isNotEmpty(),
                        fn() => new MessageResource($this->messages->first())
                    )
                ]
            ]
        ];
    }
}
