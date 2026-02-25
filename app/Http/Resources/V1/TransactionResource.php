<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'wallet-transaction',
            'id' => (string) $this->id,
            'attributes' => [
                'walletId' => (int) $this->wallet_id,
                'uuid' => $this->uuid,
                'transactionType' => $this->type,
                'amount' => $this->amountFloat,
                'amountInt' => (int) $this->amount,
                'confirmed' => (bool) $this->confirmed,
                'meta' => $this->meta,
                'bookingId' => data_get($this->meta, 'booking_id'),
                'source' => data_get($this->meta, 'source'),
                'createdAt' => optional($this->created_at)->toIso8601String(),
                'updatedAt' => optional($this->updated_at)->toIso8601String(),
            ],
        ];
    }
}
