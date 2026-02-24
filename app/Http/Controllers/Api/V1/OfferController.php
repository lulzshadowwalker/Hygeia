<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\OfferResource;
use App\Models\Booking;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Database\Eloquent\ModelNotFoundException;

#[Group('Offers')]
class OfferController extends ApiController
{
    /**
     * List offers
     *
     * Get a list of all available offers.
     */
    public function index()
    {
        $offers = Booking::with(['service', 'pricing', 'extras', 'promocode'])
            ->where('status', BookingStatus::Pending->value)
            ->get();

        return OfferResource::collection($offers);
    }

    /**
     * Get an offer
     *
     * Get the details of a specific offer.
     */
    public function show(Booking $offer)
    {
        if ($offer->status !== BookingStatus::Pending) {
            throw new ModelNotFoundException('Offer not found.');
        }

        $offer->load(['service', 'pricing', 'extras', 'promocode']);

        return OfferResource::make($offer);
    }
}
