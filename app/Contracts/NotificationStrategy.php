<?php

namespace App\Contracts;

use App\Support\PushNotification;

interface NotificationStrategy
{
    public static function send(PushNotification $notification, $notifiable): void;
    public static function isSatisfiedBy(mixed $notifiable): bool;
}
