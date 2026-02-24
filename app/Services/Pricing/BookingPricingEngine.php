<?php

namespace App\Services\Pricing;

use App\Services\Pricing\Decorators\ExtraChargesCalculatorDecorator;
use App\Services\Pricing\Decorators\PromoCodeCalculatorDecorator;

class BookingPricingEngine
{
    private readonly BookingPriceCalculator $calculator;

    public function __construct(?BookingPriceCalculator $calculator = null)
    {
        $this->calculator = $calculator ?? new PromoCodeCalculatorDecorator(
            new ExtraChargesCalculatorDecorator(
                new BaseBookingPriceCalculator
            )
        );
    }

    public function calculate(BookingPricingData $data): BookingPriceBreakdown
    {
        return $this->calculator->calculate($data);
    }
}
