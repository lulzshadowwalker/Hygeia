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

    public function test_it_lists_all_invoices_for_the_current_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invoices = Invoice::factory()->count(3)->for($user)->create();
        Invoice::factory()->count(2)->create();
        $resource = InvoiceResource::collection($invoices);

        $response = $this->getJson(route('api.v1.invoices.index'));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_it_shows_single_invoice_for_the_current_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $invoice = Invoice::factory()->for($user)->create();
        $resource = InvoiceResource::make($invoice);

        $response = $this->getJson(route('api.v1.invoices.show', $invoice));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_user_cannot_see_other_users_invoice(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $otherUserInvoice = Invoice::factory()->create();

        $response = $this->getJson(route('api.v1.invoices.show', $otherUserInvoice));
        $response->assertForbidden();
    }
}
