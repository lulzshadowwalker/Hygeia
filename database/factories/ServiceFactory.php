<?php

namespace Database\Factories;

use App\Enums\ServicePricingModel;
use App\Enums\ServiceType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends BaseFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->localized(fn (): string => $this->faker->word()),
            'type' => ServiceType::Commercial,
            'pricing_model' => ServicePricingModel::AreaRange,
            'price_per_meter' => null,
            'min_area' => null,
            'currency' => 'HUF',
        ];
    }

    public function residential(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => ServiceType::Residential,
            'pricing_model' => ServicePricingModel::AreaRange,
            'price_per_meter' => null,
            'min_area' => null,
        ]);
    }

    public function commercialPerMeter(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => ServiceType::Commercial,
            'pricing_model' => ServicePricingModel::PricePerMeter,
            'price_per_meter' => $this->faker->randomFloat(2, 10, 100),
            'min_area' => $this->faker->numberBetween(5, 30),
        ]);
    }
}
