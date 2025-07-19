<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => 'faq',
            'id' => (string) $this->id,
            'attributes' => [
                'question' => $this->question,
                'answer' => $this->answer,
            ],
        ];
    }
}
