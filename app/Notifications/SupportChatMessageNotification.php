<?php

namespace App\Notifications;

use App\Models\Message;
use App\Support\PushNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportChatMessageNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Message $message)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->preferences->notificationChannels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('notifications.support-chat-message.title'))
            ->greeting(__('notifications.support-chat-message.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.support-chat-message.line1'))
            ->line(__('notifications.support-chat-message.line2'));
    }

    public function toPush(object $notifiable): PushNotification
    {
        return new PushNotification(
            // title: __('notifications.support-chat-message.title'),
            // better way to get translation for specific locale:
            title: trans('notifications.support-chat-message.title', [], $notifiable->preferences->language),
            body: $this->message->content,

        );
    }
}
