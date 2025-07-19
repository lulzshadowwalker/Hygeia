<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\FaqResource;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_faqs(): void
    {
        Faq::factory()->count(3)->create();
        $resource = FaqResource::collection(Faq::all());

        $response = $this->getJson(route('api.v1.faq.index'));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_shows_single_faq(): void
    {
        $faq = Faq::factory()->create();
        $resource = FaqResource::make($faq);

        $response = $this->getJson(route('api.v1.faq.show', $faq));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }
}
