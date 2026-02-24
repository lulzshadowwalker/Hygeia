<?php

namespace Tests\Unit\Services\Pricing;

use App\Enums\ServicePricingModel;
use App\Enums\ServiceType;
use App\Models\Pricing;
use App\Models\Service;
use App\Services\Pricing\BaseBookingPriceCalculator;
use App\Services\Pricing\BookingPricingData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class BaseBookingPriceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_residential_area_range_base_price(): void
    {
        $service = Service::factory()->residential()->create();
        $pricing = Pricing::factory()->for($service)->create([
            'amount' => 5100,
        ]);

        $calculator = new BaseBookingPriceCalculator;
        $breakdown = $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: $pricing,
            area: null,
            extras: collect(),
            currency: 'HUF',
        ));

        $this->assertSame('5100.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('0.00', $breakdown->extrasAmount->getAmount()->__toString());
        $this->assertSame('5100.00', $breakdown->totalAmount->getAmount()->__toString());
        $this->assertSame('HUF', $breakdown->currency);
    }

    public function test_it_calculates_commercial_area_range_base_price(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Commercial,
            'pricing_model' => ServicePricingModel::AreaRange,
        ]);
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

    public function test_it_calculates_commercial_price_per_meter_base_price(): void
    {
        $service = Service::factory()->commercialPerMeter()->create([
            'price_per_meter' => 125,
            'min_area' => 10,
        ]);

        $calculator = new BaseBookingPriceCalculator;
        $breakdown = $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: null,
            area: 20,
            extras: collect(),
            currency: 'HUF',
        ));

        $this->assertSame('2500.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('2500.00', $breakdown->totalAmount->getAmount()->__toString());
    }

    public function test_it_throws_when_area_is_below_service_min_area_for_per_meter_pricing(): void
    {
        $service = Service::factory()->commercialPerMeter()->create([
            'price_per_meter' => 125,
            'min_area' => 15,
        ]);

        $calculator = new BaseBookingPriceCalculator;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Area is below the minimum allowed for this service.');

        $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: null,
            area: 10,
            extras: collect(),
            currency: 'HUF',
        ));
    }
}
