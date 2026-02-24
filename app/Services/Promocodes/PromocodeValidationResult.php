<?php

namespace App\Services\Promocodes;

use App\Enums\PromocodeValidationReason;
use App\Models\Promocode;

class PromocodeValidationResult
{
    public function __construct(
        public readonly bool $valid,
        public readonly ?PromocodeValidationReason $reason = null,
        public readonly ?Promocode $promocode = null,
    ) {}

    public static function valid(Promocode $promocode): self
    {
        return new self(valid: true, reason: null, promocode: $promocode);
    }

    public static function invalid(PromocodeValidationReason $reason): self
    {
        return new self(valid: false, reason: $reason, promocode: null);
    }
}
