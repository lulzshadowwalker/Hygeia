<?php

namespace App\Observers;

use App\Enums\UserStatus;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\NewOfferCreatedNotification;
use Illuminate\Support\Facades\Notification;

class BookingObserver
{
    public function created(Booking $booking): void
    {
        if (! $booking->status->isPending()) {
            return;
        }

        $cleaners = User::cleaners()
            ->where('status', UserStatus::Active)
            ->with(['preferences', 'deviceTokens'])
            ->get();

        if ($cleaners->isEmpty()) {
            return;
        }

        Notification::send(
            $cleaners,
            new NewOfferCreatedNotification($booking->loadMissing('service')),
        );
    }
}
