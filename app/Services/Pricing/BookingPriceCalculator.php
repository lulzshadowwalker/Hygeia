<?php

namespace App\Services\Pricing;

interface BookingPriceCalculator
{
    public function calculate(BookingPricingData $data): BookingPriceBreakdown;
}
