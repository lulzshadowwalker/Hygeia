<?php

namespace App\Services\Pricing;

use App\Models\Pricing;
use App\Models\Service;
use Illuminate\Support\Collection;

class BookingPricingData
{
    public function __construct(
        public readonly Service $service,
        public readonly ?Pricing $pricing,
        public readonly ?float $area,
        public readonly Collection $extras,
        public readonly string $currency = 'HUF',
    ) {}
}
