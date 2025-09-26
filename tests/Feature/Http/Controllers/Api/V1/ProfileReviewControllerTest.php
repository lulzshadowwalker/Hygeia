<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class ProfileReviewControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_lists_all_profile_reviews(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $reviews = Review::factory()->count(3)->create([
            'reviewable_type' => Cleaner::class,
            'reviewable_id' => $cleaner->id,
            'user_id' => Client::factory()->create()->user->id,
        ]);
        $this->actingAs($cleaner->user);

        $resource = ReviewResource::collection($reviews);

        $this->getJson(route('api.v1.profile.reviews.index', ['cleaner' => $cleaner]))
            ->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }
}
