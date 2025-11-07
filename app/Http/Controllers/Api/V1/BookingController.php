<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Filters\BookingFilter;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreBookingRequest;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;
use App\Models\Extra;
use App\Models\Pricing;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Group('Bookings')]
class BookingController extends ApiController
{
    /**
     * List bookings
     *
     * Get a list of all bookings for the authenticated user.
     */
    public function index(BookingFilter $filters)
    {
        $this->authorize('viewAny', Booking::class);

        $query = Booking::with([
            'client',
            'service',
            'pricing',
            'extras',
        ])->filter($filters);

        if (auth()->user()->isClient) {
            $query->where('client_id', auth()->user()->client->id);
        } elseif (auth()->user()->isCleaner) {
            $query->where('cleaner_id', auth()->user()->cleaner->id);
        }

        $bookings = $query->get();

        return BookingResource::collection($bookings);
    }

    /**
     * Get a booking
     *
     * Get the details of a specific booking.
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return BookingResource::make($booking);
    }

    /**
     * Create a booking
     *
     * Create a new booking.
     */
    public function store(StoreBookingRequest $request)
    {
        $this->authorize('create', Booking::class);

        return DB::transaction(function () use ($request) {
            $pricing = Pricing::findOrFail($request->pricingId());

            $booking = Booking::create([
                'client_id' => Auth::user()->client->id,
                'service_id' => $request->serviceId(),
                'pricing_id' => $request->pricingId(),
                'selected_amount' => $pricing->amount,
                'urgency' => $request->urgency()->value,
                'scheduled_at' => $request->scheduledAt(),
                'has_cleaning_material' => $request->hasCleaningMaterials(),
                'location' => $request->location(),
                'lat' => $request->lat(),
                'lng' => $request->lng(),

                //  TODO: Calculate booking price action class
                'amount' => $pricing->amount,
                'status' => BookingStatus::Pending,
            ]);

            foreach ($request->extraIds() as $extraId) {
                $extra = Extra::findOrFail($extraId);
                $booking
                    ->extras()
                    ->attach($extraId, ['amount' => $extra->amount]);
            }

            return BookingResource::make($booking);
        });
    }
}
