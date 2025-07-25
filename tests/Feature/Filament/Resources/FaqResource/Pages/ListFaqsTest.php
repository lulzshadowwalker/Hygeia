<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Filament\Resources\FaqResource\Pages\ListFaqs;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;

class ListFaqsTest extends TestCase
{
    use RefreshDatabase, WithAdmin;

    public function test_it_renders_the_page()
    {
        $this->get(FaqResource::getUrl('index'))->assertOk();
    }

    public function test_page_contains_faq_records()
    {
        $items = Faq::factory()->count(5)->create();

        Livewire::test(ListFaqs::class)
            ->assertCanSeeTableRecords($items);
    }
}
