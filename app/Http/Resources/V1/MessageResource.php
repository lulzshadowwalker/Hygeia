<?php

namespace App\Http\Resources\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

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
                'content' => (string) $this->content,
                'type' => $this->type,
                'mine' => (bool) $this->mine,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            'relationships' => [
                'sender' =>  ParticipantResource::make($this->user),
            ]
        ];
    }
}
