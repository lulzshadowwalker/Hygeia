<?php

namespace Database\Factories;

class PageFactory extends BaseFactory
{
    public function definition(): array
    {
        return [
            'title' => $this->localized(fn () => $this->faker->sentence),
            'content' => $this->localized(fn () => $this->faker->paragraph(12)),
        ];
    }
}
