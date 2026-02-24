<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use App\Notifications\Channels\PushChannel;
use App\Support\PushNotification;
use Brick\Money\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOfferCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    public function via(object $notifiable): array
    {
        if (! $notifiable instanceof User) {
            return [];
        }

        $channels = [];

        if ($notifiable->wantsEmailNotifications()) {
            $channels[] = 'mail';
        }

        if ($notifiable->wantsPushNotifications()) {
            $channels[] = PushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable instanceof User
            ? $notifiable->notificationPreferences()->language->value
            : app()->getLocale();

        $serviceName = $this->booking->service?->name ?? trans('notifications.new-offer.default-service', [], $locale);

        return (new MailMessage)
            ->subject(trans('notifications.new-offer.title', [], $locale))
            ->greeting(trans('notifications.new-offer.greeting', ['name' => $notifiable->name], $locale))
            ->line(trans('notifications.new-offer.mail-line', [
                'service' => $serviceName,
                'amount' => $this->bookingAmount(),
                'currency' => $this->bookingCurrency(),
            ], $locale))
            ->line(trans('notifications.new-offer.mail-footnote', [], $locale));
    }

    public function toPush(object $notifiable): PushNotification
    {
        $locale = $notifiable instanceof User
            ? $notifiable->notificationPreferences()->language->value
            : app()->getLocale();

        $serviceName = $this->booking->service?->name ?? trans('notifications.new-offer.default-service', [], $locale);

        return new PushNotification(
            title: trans('notifications.new-offer.title', [], $locale),
            body: trans('notifications.new-offer.push-body', [
                'service' => $serviceName,
                'amount' => $this->bookingAmount(),
                'currency' => $this->bookingCurrency(),
            ], $locale),
        );
    }

    private function bookingAmount(): string
    {
        if ($this->booking->amount instanceof Money) {
            return $this->booking->amount->getAmount()->__toString();
        }

        return (string) $this->booking->amount;
    }

    private function bookingCurrency(): string
    {
        return $this->booking->currency ?? 'HUF';
    }
}
