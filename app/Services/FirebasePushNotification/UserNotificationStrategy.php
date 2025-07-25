<?php

namespace App\Services\FirebasePushNotification;

use App\Contracts\NotificationStrategy;
use App\Traits\InteractsWithFirebase;
use App\Models\User;
use App\Support\PushNotification;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserNotificationStrategy implements NotificationStrategy
{
    use InteractsWithFirebase;

    /**
     * @param Object|array<Object> $notifiable
     */
    public static function send(PushNotification $notification, $notifiable): void
    {
        if (! is_array($notifiable)) $notifiable = [$notifiable];

        foreach ($notifiable as $n) {
            foreach ($n->deviceTokens as $t) {
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . self::accessToken()])
                    ->post(self::endpoint('messages:send'), [
                        'message' => [
                            'data' => (object) [],
                            'notification' => [
                                'title' => $notification->title,
                                'body' => $notification->body,
                                'image' => $notification->image,
                            ],
                            'token' => $t->token,
                        ],
                    ]);

                if ($response->ok()) continue;

                Log::error('Failed to send notification', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                throw new Exception('Failed to send notification');
            }
        }
    }

    public static function isSatisfiedBy(mixed $notifiable): bool
    {
        if ($notifiable instanceof User) return true;

        if (is_array($notifiable) && count($notifiable) > 0 && $notifiable[0] instanceof User) return true;

        return false;
    }
}
