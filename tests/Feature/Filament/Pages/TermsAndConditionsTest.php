<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\TermsAndConditions;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;
use Tests\Traits\WithPages;

class TermsAndConditionsTest extends TestCase
{
    use RefreshDatabase, WithAdmin, WithPages;

    public function test_it_renders_the_page(): void
    {
        $this->get(route('filament.admin.pages.terms-and-conditions'))
            ->assertOk();
    }

    public function test_it_updates_the_page(): void
    {
        Livewire::test(TermsAndConditions::class)
            ->fillForm([
                'content' => 'Test Content',
            ])
            ->call('publish');

        $page = Page::where('slug', Page::TERMS_AND_CONDITIONS)->first();
        $this->assertEquals('Test Content', $page->content);
    }
}
