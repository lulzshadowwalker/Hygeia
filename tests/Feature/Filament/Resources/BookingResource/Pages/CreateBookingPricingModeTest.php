<?php

namespace Tests\Feature\Filament\Resources\BookingResource\Pages;

use App\Enums\ServicePricingModel;
use App\Filament\Resources\BookingResource\Pages\CreateBooking;
use App\Models\Pricing;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;

class CreateBookingPricingModeTest extends TestCase
{
    use RefreshDatabase, WithAdmin;

    public function test_it_recalculates_amounts_for_area_range_services_using_selected_pricing(): void
    {
        $service = Service::factory()->residential()->create();
        $pricing = Pricing::factory()->for($service)->create([
            'amount' => 3200,
        ]);

        Livewire::test(CreateBooking::class)
            ->fillForm([
                'service_id' => $service->id,
                'pricing_id' => $pricing->id,
            ])
            ->assertSet('data.selected_amount', '3200.00')
            ->assertSet('data.amount', '3200.00');
    }

    public function test_it_recalculates_amounts_for_price_per_meter_services_using_area(): void
    {
        $service = Service::factory()->commercialPerMeter()->create([
            'pricing_model' => ServicePricingModel::PricePerMeter,
            'price_per_meter' => 120,
            'min_area' => 10,
        ]);

        Livewire::test(CreateBooking::class)
            ->fillForm([
                'service_id' => $service->id,
                'area' => 25,
            ])
            ->assertSet('data.selected_amount', '3000.00')
            ->assertSet('data.amount', '3000.00');
    }
}
