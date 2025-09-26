<?php

namespace Database\Factories;

use App\Models\Faq;

class FaqFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'question' => $this->localized(fn (): string => $this->faker->sentence(6, true)),
            'answer' => $this->localized(fn (): string => $this->faker->paragraph(3, true)),
        ];
    }
}
