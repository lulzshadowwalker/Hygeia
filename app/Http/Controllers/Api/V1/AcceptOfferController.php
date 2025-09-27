<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;

#[Group('Offers')]
class AcceptOfferController extends ApiController
{
    /**
     * Accept an offer
     *
     * Accept a specific offer.
     */
    public function store(Booking $offer)
    {
        $this->isAble('accept', $offer);

        $offer->cleaner_id = Auth::user()->cleaner->id;
        $offer->status = BookingStatus::Confirmed;
        $offer->save();

        return BookingResource::make($offer);
    }
}
