<?php

namespace App\Observers;

use App\Enums\SupportTicketStatus;
use App\Events\SupportTicketReceived;
use App\Models\SupportTicket;

class SupportTicketObserver
{
    public function creating(SupportTicket $supportTicket): void
    {
        $supportTicket->number = strtoupper(uniqid('TICKET-'));

        if (! $supportTicket->status) {
            $supportTicket->status = SupportTicketStatus::Open;
        }
    }

    public function created(SupportTicket $supportTicket): void
    {
        SupportTicketReceived::dispatch($supportTicket);
    }
}
