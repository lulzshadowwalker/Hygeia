<?php

namespace Database\Factories;

class ExtraFactory extends BaseFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->localized(fn(): string => $this->faker->word()),
            'amount' => $this->faker->randomFloat(2, 50, 99),
        ];
    }
}
