<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\AboutUs;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;
use Tests\Traits\WithPage;

class AboutUsTest extends TestCase
{
    use RefreshDatabase, WithAdmin, WithPage;

    public function test_it_renders_the_page(): void
    {
        $this->get(route('filament.admin.pages.about-us'))
            ->assertOk();
    }

    public function test_it_updates_the_page(): void
    {
        Livewire::test(AboutUs::class)
            ->fillForm([
                'content' => 'Test Content',
            ])
            ->call('publish');

        $page = Page::where('slug', Page::ABOUT_US)->first();
        $this->assertEquals('Test Content', $page->content);
    }
}
