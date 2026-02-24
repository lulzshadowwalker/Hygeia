<?php

namespace Tests\Unit\Notifications;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Notifications\Channels\PushChannel;
use App\Notifications\NewOfferCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewOfferCreatedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_uses_mail_and_push_channels_when_both_preferences_are_enabled(): void
    {
        $service = Service::factory()->create();
        $booking = Booking::factory()->for($service)->pending()->create([
            'amount' => 12345.67,
            'currency' => 'HUF',
        ]);

        $cleanerUser = User::factory()->create();
        $cleanerUser->preferences()->create([
            'email_notifications' => true,
            'push_notifications' => true,
        ]);

        $notification = new NewOfferCreatedNotification($booking->load('service'));

        $this->assertSame(
            ['mail', PushChannel::class],
            $notification->via($cleanerUser),
        );
    }

    public function test_it_uses_no_channels_when_both_preferences_are_disabled(): void
    {
        $booking = Booking::factory()->pending()->create();

        $cleanerUser = User::factory()->create();
        $cleanerUser->preferences()->create([
            'email_notifications' => false,
            'push_notifications' => false,
        ]);

        $notification = new NewOfferCreatedNotification($booking);

        $this->assertSame([], $notification->via($cleanerUser));
    }
}
