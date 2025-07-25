<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\PrivacyPolicy;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;
use Tests\Traits\WithPages;

class PrivacyPolicyTest extends TestCase
{
    use RefreshDatabase, WithAdmin, WithPages;

    public function test_it_renders_the_page(): void
    {
        $this->get(route('filament.admin.pages.privacy-policy'))
            ->assertOk();
    }

    public function test_it_updates_the_page(): void
    {
        Livewire::test(PrivacyPolicy::class)
            ->fillForm([
                'content' => 'Test Content',
            ])
            ->call('publish');

        $page = Page::where('slug', Page::PRIVACY_POLICY)->first();
        $this->assertEquals('Test Content', $page->content);
    }
}
