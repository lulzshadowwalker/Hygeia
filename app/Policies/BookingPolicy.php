<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        return $user->isClient && $booking->client_id === $user->client->id;
    }

    public function create(User $user): bool
    {
        return $user->isClient;
    }

    public function viewAny(User $user): bool
    {
        return $user->isClient;
    }

    // Accept offer
    public function accept(User $user, Booking $booking): bool
    {
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
        if (! $user->isClient) {
            return false;
        }

        if ($user->client->id !== $booking->client_id) {
            return false;
        }

        return true;
    }
}
