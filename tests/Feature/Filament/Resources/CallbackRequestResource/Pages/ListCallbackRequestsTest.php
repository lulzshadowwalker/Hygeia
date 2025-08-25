<?php

namespace Tests\Feature\Filament\Resources\CallbackRequestResource\Pages;

use App\Filament\Resources\CallbackRequestResource;
use App\Filament\Resources\CallbackRequestResource\Pages\ListCallbackRequests;
use App\Models\CallbackRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;

class ListCallbackRequestsTest extends TestCase
{
    use RefreshDatabase, WithAdmin;

    public function test_it_renders_the_page()
    {
        $this->get(CallbackRequestResource::getUrl('index'))->assertOk();
    }

    public function test_page_contains_callback_request_records()
    {
        $item = CallbackRequest::factory()->count(5)->create();

        Livewire::test(ListCallbackRequests::class)
            ->assertCanSeeTableRecords($item);
    }
}
