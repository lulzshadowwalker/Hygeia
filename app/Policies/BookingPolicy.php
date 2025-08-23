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
}
