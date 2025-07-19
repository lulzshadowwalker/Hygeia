<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Resources\V1\SupportTicketResource;
use App\Models\Client;
use App\Models\Faq;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class SupportTicketControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_lists_all_support_tickets_for_the_current_user(): void
    {
        $user = User::factory()->has(Client::factory())->create();
        $user->assignRole(Role::Client->value);
        $this->actingAs($user);

        $tickets = SupportTicket::factory()->count(3)->for($user)->create();
        $resource = SupportTicketResource::collection($tickets);

        $response = $this->getJson(route('api.v1.support-tickets.index'));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));;
    }

    public function test_it_shows_a_single_support_ticket_for_the_current_user(): void
    {
        $user = User::factory()->has(Client::factory())->create();
        $user->assignRole(Role::Client->value);
        $this->actingAs($user);

        $ticket = SupportTicket::factory()->for($user)->create();
        $resource = SupportTicketResource::make($ticket);

        $response = $this->getJson(route('api.v1.support-tickets.show', ['supportTicket' => $ticket]));
        $response->assertOk()
            ->assertExactJson($resource->response()->getData(true));
    }

    public function test_user_cannot_see_other_users_support_tickets(): void
    {
        $user = User::factory()->has(Client::factory())->create();
        $user->assignRole(Role::Client->value);
        $this->actingAs($user);

        $otherUserTicket = SupportTicket::factory()->create();

        $response = $this->getJson(route('api.v1.support-tickets.show', ['supportTicket' => $otherUserTicket]));
        $response->assertForbidden();
    }
}
