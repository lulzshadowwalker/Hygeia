<?php

namespace Tests\Feature\Filament\Resources\ServiceResource\Pages;

use App\Enums\ServicePricingModel;
use App\Enums\ServiceType;
use App\Filament\Resources\ServiceResource\Pages\CreateService;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;

class CreateServicePricingModelTest extends TestCase
{
    use RefreshDatabase, WithAdmin;

    public function test_it_requires_price_per_meter_and_min_area_for_commercial_per_meter_services(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Office Cleaning',
                'type' => ServiceType::Commercial->value,
                'pricing_model' => ServicePricingModel::PricePerMeter->value,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'price_per_meter' => 'required',
                'min_area' => 'required',
            ]);
    }

    public function test_it_forces_residential_services_to_area_range_pricing_model(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm([
                'name' => 'Home Cleaning',
                'type' => ServiceType::Residential->value,
                'pricing_model' => ServicePricingModel::PricePerMeter->value,
                'price_per_meter' => 150,
                'min_area' => 10,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $service = Service::query()->latest('id')->firstOrFail();

        $this->assertSame(ServiceType::Residential, $service->type);
        $this->assertSame(ServicePricingModel::AreaRange, $service->pricing_model);
        $this->assertNull($service->price_per_meter);
        $this->assertNull($service->min_area);
    }
}
