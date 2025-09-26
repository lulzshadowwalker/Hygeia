<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class AcceptBookingController extends ApiController
{
    public function store(Booking $booking)
    {
        $this->isAble('accept', $booking);

        $booking->cleaner_id = Auth::user()->cleaner->id;
        $booking->status = BookingStatus::Confirmed;
        $booking->save();

        return BookingResource::make($booking);
    }
}
