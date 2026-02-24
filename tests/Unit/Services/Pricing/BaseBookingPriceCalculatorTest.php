<?php

namespace Tests\Unit\Services\Pricing;

use App\Enums\ServiceType;
use App\Models\Pricing;
use App\Models\Service;
use App\Services\Pricing\BaseBookingPriceCalculator;
use App\Services\Pricing\BookingPricingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaseBookingPriceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_residential_base_price(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Residential,
            'price_per_meter' => 100,
        ]);

        $calculator = new BaseBookingPriceCalculator;
        $breakdown = $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: null,
            area: 50,
            extras: collect(),
            currency: 'HUF',
        ));

        $this->assertSame('5000.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('0.00', $breakdown->extrasAmount->getAmount()->__toString());
        $this->assertSame('5000.00', $breakdown->totalAmount->getAmount()->__toString());
        $this->assertSame('HUF', $breakdown->currency);
    }

    public function test_it_calculates_non_residential_base_price(): void
    {
        $service = Service::factory()->create(['type' => ServiceType::Commercial]);
        $pricing = Pricing::factory()->for($service)->create([
            'amount' => 4200,
        ]);

        $calculator = new BaseBookingPriceCalculator;
        $breakdown = $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: $pricing,
            area: null,
            extras: collect(),
            currency: 'HUF',
        ));

        $this->assertSame('4200.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('0.00', $breakdown->extrasAmount->getAmount()->__toString());
        $this->assertSame('4200.00', $breakdown->totalAmount->getAmount()->__toString());
        $this->assertSame('HUF', $breakdown->currency);
    }
}
