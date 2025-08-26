<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\OfferResource;
use App\Models\Booking;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OfferController extends ApiController
{
    public function index()
    {
        $offers = Booking::with(['service', 'pricing', 'extras'])
            ->where('status', BookingStatus::Pending->value)
            ->get();

        return OfferResource::collection($offers);
    }

    public function show(Booking $offer)
    {
        if ($offer->status !== BookingStatus::Pending) {
            throw new ModelNotFoundException('Offer not found.');
        }

        return OfferResource::make($offer);
    }
}
