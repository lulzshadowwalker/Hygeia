<?php

namespace App\Services\FirebasePushNotification;

use App\Contracts\NotificationStrategy;
use App\Contracts\PushNotificationService;
use App\Support\NotificationStrategyCollection;
use App\Support\PushNotification;
use InvalidArgumentException;

class FirebasePushNotificationService implements PushNotificationService
{
    protected ?string $title;
    protected ?string $body;
    protected ?NotificationStrategy $strategy;
    protected NotificationStrategyCollection $strategies;
    protected mixed $notifiable;

    public function __construct()
    {
        $this->strategies = NotificationStrategyCollection::make([
            new UserNotificationStrategy,
            new AudienceNotificationStrategy,
        ]);
    }

    public static function make(): self
    {
        return new self;
    }

    public function title(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param Object|array<Object>|Audience $notifiable
     *
     * @return self
     */
    public function to($notifiable): self
    {
        $this->strategy = $this->strategies->match($notifiable);
        if (! $this->strategy) throw new InvalidArgumentException('Unrecognized notifiable');

        $this->notifiable = $notifiable;
        return $this;
    }

    public function send(): void
    {
        if (! $this->title) {
            throw new InvalidArgumentException('Notification title cannot be empty');
        }

        if (! $this->body) {
            throw new InvalidArgumentException('Notification body cannot be empty');
        }

        if (! $this->notifiable) {
            throw new InvalidArgumentException('Notifiable cannot be empty');
        }

        $this->strategy->send(
            new PushNotification(title: $this->title, body: $this->body),
            $this->notifiable,
        );
    }
}
