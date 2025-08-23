<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreBookingRequest;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;
use App\Models\Extra;
use App\Models\Pricing;
use Illuminate\Support\Facades\DB;

class BookingController extends ApiController
{
    public function index()
    {
        $this->authorize('viewAny', Booking::class);

        $bookings = Booking::with(['client', 'service', 'pricing', 'extras'])
            ->where('client_id', auth()->user()->client->id)
            ->get();

        return BookingResource::collection($bookings);
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);
        return BookingResource::make($booking);
    }

    public function store(StoreBookingRequest $request)
    {
        $this->authorize('create', Booking::class);

        return DB::transaction(function () use ($request) {
            $pricing = Pricing::findOrFail($request->pricingId());

            $booking = Booking::create([
                'client_id' => $request->user()->client->id,
                'service_id' => $request->serviceId(),
                'pricing_id' => $request->pricingId(),
                'selected_amount' => $pricing->amount,
                'urgency' => $request->urgency()->value,
                'scheduled_at' => $request->scheduledAt(),
                'has_cleaning_material' => $request->hasCleaningMaterials(),

                //  TODO: Calculate booking price action class
                'amount' => $pricing->amount,
                'status' => BookingStatus::Pending,
            ]);

            foreach ($request->extraIds() as $extraId) {
                $extra = Extra::findOrFail($extraId);
                $booking->extras()->attach($extraId, ['amount' => $extra->amount]);
            }

            return BookingResource::make($booking);
        });
    }
}
