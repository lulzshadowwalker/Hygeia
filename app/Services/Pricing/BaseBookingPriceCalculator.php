<?php

namespace App\Services\Pricing;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use InvalidArgumentException;

class BaseBookingPriceCalculator implements BookingPriceCalculator
{
    public function calculate(BookingPricingData $data): BookingPriceBreakdown
    {
        if ($data->service->usesAreaRangePricing()) {
            if ($data->pricing === null || $data->pricing->amount === null) {
                throw new InvalidArgumentException('Pricing is required for area-range pricing.');
            }

            $selectedAmount = $data->pricing->amount;
        } else {
            if ($data->area === null || $data->service->price_per_meter === null) {
                throw new InvalidArgumentException('Area and price per meter are required for per-meter pricing.');
            }

            if ($data->service->min_area !== null && $data->area < $data->service->min_area) {
                throw new InvalidArgumentException('Area is below the minimum allowed for this service.');
            }

            $selectedAmount = $data->service->price_per_meter->multipliedBy((string) $data->area, RoundingMode::HALF_UP);
        }

        $currency = strtoupper($selectedAmount->getCurrency()->getCurrencyCode());
        $extrasAmount = Money::zero($currency);

        return new BookingPriceBreakdown(
            selectedAmount: $selectedAmount,
            extrasAmount: $extrasAmount,
            totalAmount: $selectedAmount,
            currency: $currency,
        );
    }
}
