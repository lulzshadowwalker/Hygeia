<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Http\Resources\V1\InvoiceResource;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_all_invoices(): void
    {
        $this->actingAs(User::factory()->create());

        Invoice::factory()->count(3)->create();
        $resource = InvoiceResource::collection(Invoice::all());

        $response = $this->getJson(route('api.v1.invoices.index'));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_shows_single_invoice(): void
    {
        $this->actingAs(User::factory()->create());

        $invoice = Invoice::factory()->create();
        $resource = InvoiceResource::make($invoice);

        $response = $this->getJson(route('api.v1.invoices.show', $invoice));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }
}
