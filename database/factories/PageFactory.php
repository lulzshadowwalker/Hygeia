<?php

namespace Database\Factories;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends BaseFactory
{
    public function definition(): array
    {
        return [
            'title' => $this->localized(fn() => $this->faker->sentence),
            'content' => $this->localized(fn() => $this->faker->paragraph(12)),
        ];
    }
}
