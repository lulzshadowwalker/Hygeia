<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->isAdmin) {
            return true;
        }

        return $user->isClient && $booking->client_id === $user->client->id;
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin) {
            return true;
        }

        return $user->isClient;
    }

    public function viewAny(User $user): bool
    {
        if ($user->isAdmin) {
            return true;
        }

        return $user->isClient || $user->isCleaner;
    }

    // Accept offer
    public function accept(User $user, Booking $booking): bool
    {
        if ($user->isAdmin) {
            return true;
        }
        if (! $user->isCleaner) {
            return false;
        }

        $status = $booking->status;
        if ($status->isCancelled() || $status->isCompleted() || $status->isConfirmed()) {
            return false;
        }

        return true;
    }

    public function complete(User $user, Booking $booking): bool
    {
        if ($user->isAdmin) {
            return true;
        }
        if (! $user->isCleaner) {
            return false;
        }

        if ($user->cleaner->id !== $booking->cleaner_id) {
            return false;
        }

        return true;
    }

    public function confirmCashReceived(User $user, Booking $booking): bool
    {
        if ($user->isAdmin) {
            return true;
        }

        if (! $user->isCleaner) {
            return false;
        }

        return $user->cleaner->id === $booking->cleaner_id;
    }
}
