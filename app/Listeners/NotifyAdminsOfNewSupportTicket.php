<?php

namespace App\Listeners;

use App\Events\SupportTicketReceived;
use App\Models\User;
use App\Notifications\AdminSupportTicketReceivedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyAdminsOfNewSupportTicket implements ShouldQueue
{
    public function handle(SupportTicketReceived $event): void
    {
        Notification::send(
            User::admins()->get(),
            new AdminSupportTicketReceivedNotification($event->supportTicket)
        );
    }
}
