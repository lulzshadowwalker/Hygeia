<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Invoice $invoice)
    {
        //
    }

    public function via(object $notifiable): array
    {
        if ($notifiable instanceof User && $notifiable->wantsEmailNotifications()) {
            return ['mail'];
        }

        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        //  TODO: localized notification message based on user preferences
        return (new MailMessage)
            ->subject('Thank you for your payment')
            ->line('We have received your payment.')
            ->attach($this->invoice->filepath());
    }
}
