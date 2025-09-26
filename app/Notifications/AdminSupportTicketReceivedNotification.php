<?php

namespace App\Notifications;

use App\Filament\Resources\SupportTicketResource;
use App\Models\SupportTicket;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminSupportTicketReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SupportTicket $supportTicket)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('New support ticket has been received.')
            ->action('View Ticket', SupportTicketResource::getUrl('edit', ['record' => $this->supportTicket]));
    }

    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('New support ticket has been received.')
            ->actions([
                Action::make('go-to-ticket')
                    ->button()
                    ->label('View Ticket')
                    ->url(SupportTicketResource::getUrl('edit', ['record' => $this->supportTicket])),
            ])
            ->icon(SupportTicketResource::getNavigationIcon())
            ->getDatabaseMessage();
    }
}
