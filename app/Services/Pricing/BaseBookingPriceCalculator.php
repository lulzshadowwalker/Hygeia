<?php

namespace App\Services\Pricing;

use App\Enums\ServiceType;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use InvalidArgumentException;

class BaseBookingPriceCalculator implements BookingPriceCalculator
{
    public function calculate(BookingPricingData $data): BookingPriceBreakdown
    {
        if ($data->service->type === ServiceType::Residential) {
            if ($data->area === null || $data->service->price_per_meter === null) {
                throw new InvalidArgumentException('Area and price per meter are required for residential pricing.');
            }

            $selectedAmount = $data->service->price_per_meter->multipliedBy((string) $data->area, RoundingMode::HALF_UP);
        } else {
            if ($data->pricing === null || $data->pricing->amount === null) {
                throw new InvalidArgumentException('Pricing is required for non-residential services.');
            }

            $selectedAmount = $data->pricing->amount;
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
