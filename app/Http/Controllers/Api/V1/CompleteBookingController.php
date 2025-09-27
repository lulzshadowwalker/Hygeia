<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;
use Dedoc\Scramble\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('Bookings')]
class CompleteBookingController extends ApiController
{
    /**
     * Mark a booking as complete
     *
     * Mark a specific booking as complete.
     */
    public function store(Booking $booking)
    {
        $this->isAble('complete', $booking);

        if ($booking->status->isCompleted()) {
            return $this->response->error(
                title: 'Booking Already Completed',
                detail: 'This booking has already been completed.',
                code: Response::HTTP_BAD_REQUEST
            )->build(Response::HTTP_BAD_REQUEST);
        }

        if (! $booking->status->isConfirmed()) {
            return $this->response->error(
                title: 'Invalid Booking Status',
                detail: 'Only confirmed bookings can be completed.',
                code: Response::HTTP_BAD_REQUEST
            )->build(Response::HTTP_BAD_REQUEST);
        }

        $booking->status = BookingStatus::Completed;
        $booking->save();

        return BookingResource::make($booking);
    }
}
