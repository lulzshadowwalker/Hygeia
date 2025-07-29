<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "type" => "message",
            "id" => (string) $this->id,
            "attributes" => [
                "content" => $this->content,
                "mine" => auth()->id() === $this->user_id,
                "type" => $this->type,
                "createdAt" => $this->created_at,
            ],
        ];
    }
}
