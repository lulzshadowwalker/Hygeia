<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

class ParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = match (true) {
            $this->resource->isAdmin => new AdminResource($this->resource),
            $this->resource->isClient => new ClientResource($this->resource),
            $this->resource->isCleaner => new CleanerResource($this->resource),
            default => throw new InvalidArgumentException('Invalid user type')
        };

        return $resource->toArray($request);
    }
}
