<?php

namespace App\Notifications;

use App\Support\DatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FakeDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return (new DatabaseNotification(
            title: 'New Notification',
            message: 'This is a fake notification',
            data: [
                'foo' => 'bar',
            ]
        ))->toArray();
    }
}
