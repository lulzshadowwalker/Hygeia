<?php

namespace Tests\Unit\Services\Pricing;

use App\Enums\ServiceType;
use App\Models\Extra;
use App\Models\Pricing;
use App\Models\Service;
use App\Services\Pricing\BookingPricingData;
use App\Services\Pricing\BookingPricingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingPricingEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_total_for_non_residential_service_with_extras(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Commercial,
        ]);

        $pricing = Pricing::factory()->for($service)->create([
            'amount' => 3000,
        ]);

        $extras = Extra::factory()->count(2)->create([
            'amount' => 250,
        ]);

        $engine = new BookingPricingEngine;
        $breakdown = $engine->calculate(new BookingPricingData(
            service: $service,
            pricing: $pricing,
            area: null,
            extras: $extras,
            currency: 'HUF',
        ));

        $this->assertSame('3000.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('500.00', $breakdown->extrasAmount->getAmount()->__toString());
        $this->assertSame('0.00', $breakdown->discountAmount->getAmount()->__toString());
        $this->assertSame('3500.00', $breakdown->totalAmount->getAmount()->__toString());
        $this->assertSame('HUF', $breakdown->currency);
    }

    public function test_it_calculates_total_for_residential_service_with_extras(): void
    {
        $service = Service::factory()->create([
            'type' => ServiceType::Residential,
            'price_per_meter' => 120,
        ]);

        $extras = Extra::factory()->count(2)->create([
            'amount' => 300,
        ]);

        $engine = new BookingPricingEngine;
        $breakdown = $engine->calculate(new BookingPricingData(
            service: $service,
            pricing: null,
            area: 20,
            extras: $extras,
            currency: 'HUF',
        ));

        $this->assertSame('2400.00', $breakdown->selectedAmount->getAmount()->__toString());
        $this->assertSame('600.00', $breakdown->extrasAmount->getAmount()->__toString());
        $this->assertSame('0.00', $breakdown->discountAmount->getAmount()->__toString());
        $this->assertSame('3000.00', $breakdown->totalAmount->getAmount()->__toString());
        $this->assertSame('HUF', $breakdown->currency);
    }
}
