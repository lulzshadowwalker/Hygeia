<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\ReviewResource;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanerReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_cleaner_reviews(): void
    {
        $client = Client::factory()->create();
        $cleaner = Cleaner::factory()->create();

        $reviews = Review::factory()->count(3)->create([
            'reviewable_type' => Cleaner::class,
            'reviewable_id' => $cleaner->id,
            'user_id' => $client->user->id,
        ]);

        $resource = ReviewResource::collection($reviews);

        $this->actingAs($client->user)
            ->getJson(route('api.v1.cleaners.reviews.index', ['cleaner' => $cleaner]))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_returns_not_found_if_cleaner_does_not_exist(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($client->user)
            ->getJson(route('api.v1.cleaners.reviews.index', ['cleaner' => 999]))
            ->assertNotFound();
    }

    public function test_it_shows_cleaner_review(): void
    {
        $client = Client::factory()->create();
        $cleaner = Cleaner::factory()->create();

        $review = Review::factory()->create([
            'reviewable_type' => Cleaner::class,
            'reviewable_id' => $cleaner->id,
            'user_id' => $client->user->id,
        ]);

        $resource = ReviewResource::make($review);

        $this->actingAs($client->user)
            ->getJson(route('api.v1.cleaners.reviews.show', ['cleaner' => $cleaner, 'review' => $review]))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_returns_not_found_if_review_does_not_exist(): void
    {
        $client = Client::factory()->create();
        $cleaner = Cleaner::factory()->create();

        $this->actingAs($client->user)
            ->getJson(route('api.v1.cleaners.reviews.show', ['cleaner' => $cleaner, 'review' => 999]))
            ->assertNotFound();
    }
}
