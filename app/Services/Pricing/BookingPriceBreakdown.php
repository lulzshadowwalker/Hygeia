<?php

namespace App\Services\Pricing;

use Brick\Money\Money;

class BookingPriceBreakdown
{
    public function __construct(
        public readonly Money $selectedAmount,
        public readonly Money $extrasAmount,
        public readonly Money $discountAmount,
        public readonly Money $totalAmount,
        public readonly string $currency,
    ) {}
}
