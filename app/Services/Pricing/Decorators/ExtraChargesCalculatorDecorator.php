<?php

namespace App\Services\Pricing\Decorators;

use App\Services\Pricing\BookingPriceBreakdown;
use App\Services\Pricing\BookingPriceCalculator;
use App\Services\Pricing\BookingPricingData;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class ExtraChargesCalculatorDecorator implements BookingPriceCalculator
{
    private const CLEANING_SUPPLIES_SURCHARGE = '2000';

    public function __construct(private readonly BookingPriceCalculator $calculator) {}

    public function calculate(BookingPricingData $data): BookingPriceBreakdown
    {
        $breakdown = $this->calculator->calculate($data);
        $extrasAmount = Money::zero($breakdown->currency);

        foreach ($data->extras as $extra) {
            if ($extra->amount !== null) {
                $extrasAmount = $extrasAmount->plus($extra->amount, RoundingMode::HALF_UP);
            }
        }

        if (! $data->hasCleaningMaterials) {
            $extrasAmount = $extrasAmount->plus(
                Money::of(self::CLEANING_SUPPLIES_SURCHARGE, $breakdown->currency),
                RoundingMode::HALF_UP
            );
        }

        $combinedExtras = $breakdown->extrasAmount->plus($extrasAmount, RoundingMode::HALF_UP);
        $totalAmount = $breakdown->totalAmount->plus($extrasAmount, RoundingMode::HALF_UP);

        return new BookingPriceBreakdown(
            selectedAmount: $breakdown->selectedAmount,
            extrasAmount: $combinedExtras,
            discountAmount: $breakdown->discountAmount,
            totalAmount: $totalAmount,
            currency: $breakdown->currency,
        );
    }
}
