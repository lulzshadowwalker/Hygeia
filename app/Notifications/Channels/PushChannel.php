<?php

namespace App\Notifications\Channels;

use App\Contracts\PushNotificationService;
use App\Support\PushNotification;
use Illuminate\Notifications\Notification;

class PushChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toPush')) {
            return;
        }

        $pushNotification = $notification->toPush($notifiable);

        if (! $pushNotification instanceof PushNotification) {
            return;
        }

        app(PushNotificationService::class)::make()
            ->title($pushNotification->title)
            ->body($pushNotification->body)
            ->to($notifiable)
            ->send();
    }
}
