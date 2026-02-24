<?php

namespace Tests\Unit\Services\Pricing;

use App\Enums\ServiceType;
use App\Models\Extra;
use App\Models\Promocode;
use App\Models\Service;
use App\Services\Pricing\BaseBookingPriceCalculator;
use App\Services\Pricing\BookingPricingData;
use App\Services\Pricing\Decorators\ExtraChargesCalculatorDecorator;
use App\Services\Pricing\Decorators\PromoCodeCalculatorDecorator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoCodeCalculatorDecoratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_applies_percentage_discount_and_caps_it_by_max_discount_amount(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Residential,
            'price_per_meter' => 100,
        ]);

        $extras = Extra::factory()->count(2)->create([
            'amount' => 250,
        ]);

        $promocode = Promocode::factory()->create([
            'discount_percentage' => 50,
            'max_discount_amount' => 1000,
            'currency' => 'HUF',
        ]);

        $calculator = new PromoCodeCalculatorDecorator(
            new ExtraChargesCalculatorDecorator(new BaseBookingPriceCalculator)
        );

        $breakdown = $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: null,
            area: 100,
            extras: $extras,
            promocode: $promocode,
            currency: 'HUF',
        ));

        $this->assertSame('10000.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('500.00', $breakdown->extrasAmount->getAmount()->__toString());
        $this->assertSame('1000.00', $breakdown->discountAmount->getAmount()->__toString());
        $this->assertSame('9500.00', $breakdown->totalAmount->getAmount()->__toString());
    }

    public function test_it_clamps_discount_to_subtotal_when_max_discount_exceeds_subtotal(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Residential,
            'price_per_meter' => 100,
        ]);

        $promocode = Promocode::factory()->create([
            'discount_percentage' => 100,
            'max_discount_amount' => 10000,
            'currency' => 'HUF',
        ]);

        $calculator = new PromoCodeCalculatorDecorator(
            new ExtraChargesCalculatorDecorator(new BaseBookingPriceCalculator)
        );

        $breakdown = $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: null,
            area: 10,
            extras: collect(),
            promocode: $promocode,
            currency: 'HUF',
        ));

        $this->assertSame('1000.00', $breakdown->discountAmount->getAmount()->__toString());
        $this->assertSame('0.00', $breakdown->totalAmount->getAmount()->__toString());
    }
}
