<?php

namespace App\Contracts;

use App\Enums\Audience;

interface PushNotificationService
{
    public static function make(): self;

    public function title(string $title): self;

    public function body(string $body): self;

    /**
     * @param  object|array<object>|Audience  $notifiable
     */
    public function to($notifiable): self;

    public function send(): void;
}
