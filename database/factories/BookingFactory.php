<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\BookingUrgency;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\Pricing;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'service_id' => Service::factory(),
            'pricing_id' => Pricing::factory(),
            'price_per_meter' => $this->faker->optional()->randomFloat(2, 1, 10),
            'selected_amount' => $this->faker->randomFloat(2, 10, 100),
            'urgency' => $this->faker->randomElement(BookingUrgency::values()),
            'scheduled_at' => $this->faker->dateTimeBetween(
                '+1 days',
                '+1 month',
            ),
            'has_cleaning_material' => $this->faker->boolean(),
            'amount' => $this->faker->randomFloat(2, 10, 100),
            'status' => $this->faker->randomElement(BookingStatus::values()),
            'cleaner_id' => $this->faker->boolean() ? null : Cleaner::factory(),
            'location' => $this->faker->address,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
        ];
    }

    public function pending(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'status' => BookingStatus::Pending,
            ],
        );
    }

    public function confirmed(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'status' => BookingStatus::Confirmed,
            ],
        );
    }

    public function completed(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'status' => BookingStatus::Completed,
            ],
        );
    }

    public function cancelled(): static
    {
        return $this->state(
            fn (array $attributes) => [
                'status' => BookingStatus::Cancelled,
            ],
        );
    }
}
