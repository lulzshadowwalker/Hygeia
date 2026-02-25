<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'wallet',
            'id' => (string) data_get($this->resource, 'id'),
            'attributes' => [
                'balance' => (string) data_get($this->resource, 'balance', '0.00'),
                'currency' => (string) data_get($this->resource, 'currency', 'HUF'),
                'transactionCount' => (int) data_get($this->resource, 'transactionCount', 0),
                'creditsTotal' => (string) data_get($this->resource, 'creditsTotal', '0.00'),
                'withdrawalsTotal' => (string) data_get($this->resource, 'withdrawalsTotal', '0.00'),
                'platformFee' => (string) data_get($this->resource, 'platformFee', '0.00'),
            ],
        ];
    }
}
