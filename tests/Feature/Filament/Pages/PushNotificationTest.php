<?php

namespace Tests\Feature\Filament\Pages;

use App\Enums\Audience;
use App\Filament\Pages\PushNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;

class PushNotificationTest extends TestCase
{
    use RefreshDatabase, WithAdmin;

    public function test_it_renders_the_page(): void
    {
        $this->get(route('filament.admin.pages.push-notification'))
            ->assertOk();
    }

    public function test_it_sends_a_notification()
    {
        Livewire::test(PushNotification::class)
            ->fillForm([
                'audience' => Audience::Clients->value,
                'image' => null,
                'title' => 'Test Title',
                'body' => 'Test Body',
            ])
            ->call('publish')
            ->assertHasNoErrors();
    }
}
