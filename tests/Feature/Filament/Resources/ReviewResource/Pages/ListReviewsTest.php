<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ReviewResource\Pages\ListReviews;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Traits\WithAdmin;

class ListReviewsTest extends TestCase
{
    use RefreshDatabase, WithAdmin;

    public function test_it_renders_the_page()
    {
        $this->get(ReviewResource::getUrl('index'))->assertOk();
    }

    public function test_page_contains_review_records()
    {
        $items = Review::factory()->count(5)->create();

        Livewire::test(ListReviews::class)
            ->assertCanSeeTableRecords($items);
    }
}
