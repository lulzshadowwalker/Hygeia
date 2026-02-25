<?php

namespace Tests\Unit\Services\Pricing;

use App\Enums\ServicePricingModel;
use App\Enums\ServiceType;
use App\Models\Extra;
use App\Models\Service;
use App\Services\Pricing\BaseBookingPriceCalculator;
use App\Services\Pricing\BookingPricingData;
use App\Services\Pricing\Decorators\ExtraChargesCalculatorDecorator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtraChargesCalculatorDecoratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_adds_extras_on_top_of_base_amount(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Commercial,
            'pricing_model' => ServicePricingModel::PricePerMeter,
            'price_per_meter' => 100,
            'min_area' => 5,
        ]);

        $extras = Extra::factory()->count(2)->create([
            'amount' => 500,
        ]);

        $calculator = new ExtraChargesCalculatorDecorator(new BaseBookingPriceCalculator);
        $breakdown = $calculator->calculate(new BookingPricingData(
            service: $service,
            pricing: null,
            area: 10,
            extras: $extras,
            currency: 'HUF',
        ));

        $this->assertSame('1000.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('1000.00', $breakdown->extrasAmount->getAmount()->__toString());
        $this->assertSame('0.00', $breakdown->discountAmount->getAmount()->__toString());
        $this->assertSame('2000.00', $breakdown->totalAmount->getAmount()->__toString());
        $this->assertSame('HUF', $breakdown->currency);
    }
}
