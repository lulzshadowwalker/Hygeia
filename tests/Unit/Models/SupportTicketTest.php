<?php

namespace Tests\Unit\Models;

use App\Events\SupportTicketReceived;
use App\Models\Client;
use App\Models\SupportTicket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_a_ticket_number_on_creation(): void
    {
        $ticket = SupportTicket::factory()->create();
        $this->assertStringStartsWith('TICKET-', $ticket->number);
    }

    public function test_it_starts_with_open_status_if_status_is_not_specified(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($client->user);

        $ticket = $client->user->supportTickets()->create(['subject' => 'hello', 'message' => 'Test message']);

        $this->assertTrue($ticket->isOpen);
        $this->assertFalse($ticket->isInProgress);
        $this->assertFalse($ticket->isResolved);
    }

    public function test_status_can_be_specified(): void
    {
        $client = Client::factory()->create();
        $this->actingAs($client->user);

        $ticket = $client->user->supportTickets()->create([
            'subject' => 'hello',
            'message' => 'Test message',
            'status' => 'in-progress',
        ]);

        $this->assertFalse($ticket->isOpen);
        $this->assertTrue($ticket->isInProgress);
        $this->assertFalse($ticket->isResolved);
    }

    public function test_it_emits_support_ticket_received_event_when_created()
    {
        Event::fake(SupportTicketReceived::class);

        SupportTicket::factory()->create(['number' => 'TICKET-1234']);

        Event::assertDispatched(SupportTicketReceived::class);
    }
}
