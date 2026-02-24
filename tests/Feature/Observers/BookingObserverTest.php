<?php

namespace Tests\Feature\Observers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Cleaner;
use App\Notifications\NewOfferCreatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class BookingObserverTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_sends_new_offer_notification_to_cleaners_with_enabled_channels(): void
    {
        Notification::fake();

        $enabledCleaner = Cleaner::factory()->create();
        $enabledCleaner->user->assignRole(Role::Cleaner->value);
        $enabledCleaner->user->preferences()->create([
            'email_notifications' => true,
            'push_notifications' => false,
        ]);

        $disabledCleaner = Cleaner::factory()->create();
        $disabledCleaner->user->assignRole(Role::Cleaner->value);
        $disabledCleaner->user->preferences()->create([
            'email_notifications' => false,
            'push_notifications' => false,
        ]);

        Booking::factory()->pending()->create();

        Notification::assertSentTo(
            $enabledCleaner->user,
            NewOfferCreatedNotification::class,
        );

        Notification::assertNotSentTo(
            $disabledCleaner->user,
            NewOfferCreatedNotification::class,
        );
    }

    public function test_it_does_not_send_new_offer_notification_for_non_pending_bookings(): void
    {
        Notification::fake();

        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner->value);
        $cleaner->user->preferences()->create([
            'email_notifications' => true,
            'push_notifications' => true,
        ]);

        Booking::factory()->confirmed()->create();

        Notification::assertNotSentTo(
            $cleaner->user,
            NewOfferCreatedNotification::class,
        );
    }
}
