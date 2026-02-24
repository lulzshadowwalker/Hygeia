<?php

namespace App\Services\Pricing\Decorators;

use App\Services\Pricing\BookingPriceBreakdown;
use App\Services\Pricing\BookingPriceCalculator;
use App\Services\Pricing\BookingPricingData;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use InvalidArgumentException;

class PromoCodeCalculatorDecorator implements BookingPriceCalculator
{
    public function __construct(private readonly BookingPriceCalculator $calculator) {}

    public function calculate(BookingPricingData $data): BookingPriceBreakdown
    {
        $breakdown = $this->calculator->calculate($data);
        $promocode = $data->promocode;

        if (! $promocode) {
            return $breakdown;
        }

        $subtotal = $breakdown->totalAmount;
        $subtotalCurrency = $subtotal->getCurrency()->getCurrencyCode();
        $promoCurrency = $promocode->currency;

        if ($subtotalCurrency !== $promoCurrency) {
            throw new InvalidArgumentException('Promocode currency mismatch.');
        }

        $rawDiscount = $subtotal
            ->multipliedBy((string) $promocode->discount_percentage, RoundingMode::HALF_UP)
            ->dividedBy(100, RoundingMode::HALF_UP);

        $maxDiscount = $promocode->max_discount_amount ?? Money::zero($subtotalCurrency);
        $discount = $rawDiscount->isGreaterThan($maxDiscount) ? $maxDiscount : $rawDiscount;

        if ($discount->isGreaterThan($subtotal)) {
            $discount = $subtotal;
        }

        $totalAmount = $subtotal->minus($discount, RoundingMode::HALF_UP);

        return new BookingPriceBreakdown(
            selectedAmount: $breakdown->selectedAmount,
            extrasAmount: $breakdown->extrasAmount,
            discountAmount: $breakdown->discountAmount->plus($discount, RoundingMode::HALF_UP),
            totalAmount: $totalAmount,
            currency: $breakdown->currency,
        );
    }
}
