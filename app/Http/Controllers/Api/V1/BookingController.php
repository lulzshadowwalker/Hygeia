<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\ResponseBuilder;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PromocodeValidationReason;
use App\Filters\BookingFilter;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\V1\StoreBookingRequest;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;
use App\Models\Extra;
use App\Models\Pricing;
use App\Services\Pricing\BookingPricingData;
use App\Services\Pricing\BookingPricingEngine;
use App\Services\Promocodes\PromocodeValidator;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

#[Group('Bookings')]
class BookingController extends ApiController
{
    public function __construct(
        protected ResponseBuilder $response,
        private readonly BookingPricingEngine $pricingEngine,
        private readonly PromocodeValidator $promocodeValidator,
    ) {
        parent::__construct($response);
    }

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
            'promocode',
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
        $booking->load(['client', 'cleaner', 'service', 'pricing', 'extras', 'promocode']);

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
            $service = \App\Models\Service::findOrFail($request->serviceId());
            $pricingId = null;
            $pricing = null;
            $promocodeId = null;
            $promocode = null;

            if ($service->type !== \App\Enums\ServiceType::Residential) {
                $pricing = Pricing::findOrFail($request->pricingId());
                $pricingId = $pricing->id;
            }

            if ($request->promocode()) {
                $promocodeValidation = $this->promocodeValidator->validate(
                    $request->promocode(),
                    lockForUpdate: true,
                );

                if (! $promocodeValidation->valid) {
                    return $this->response->error(
                        title: 'Invalid Promocode',
                        detail: 'The promocode is invalid or cannot be applied.',
                        code: Response::HTTP_UNPROCESSABLE_ENTITY,
                        meta: ['reason' => $promocodeValidation->reason?->value ?? PromocodeValidationReason::Unknown->value],
                    )->build(Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $promocode = $promocodeValidation->promocode;
                $promocodeId = $promocode?->id;
            }

            $extras = Extra::query()->whereIn('id', $request->extraIds())->get();

            try {
                $breakdown = $this->pricingEngine->calculate(new BookingPricingData(
                    service: $service,
                    pricing: $pricing,
                    area: $request->area(),
                    extras: $extras,
                    promocode: $promocode,
                    currency: $service->currency ?? 'HUF',
                ));
            } catch (InvalidArgumentException) {
                return $this->response->error(
                    title: 'Invalid Promocode',
                    detail: 'The promocode cannot be applied for this booking.',
                    code: Response::HTTP_UNPROCESSABLE_ENTITY,
                    meta: ['reason' => PromocodeValidationReason::BookingNotEligible->value],
                )->build(Response::HTTP_UNPROCESSABLE_ENTITY);
            } catch (\Throwable) {
                return $this->response->error(
                    title: 'Invalid Promocode',
                    detail: 'The promocode cannot be applied for this booking.',
                    code: Response::HTTP_UNPROCESSABLE_ENTITY,
                    meta: ['reason' => PromocodeValidationReason::Unknown->value],
                )->build(Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $booking = Booking::create([
                'client_id' => Auth::user()->client->id,
                'service_id' => $request->serviceId(),
                'pricing_id' => $pricingId,
                'promocode_id' => $promocodeId,
                'selected_amount' => $breakdown->selectedAmount,
                'area' => $request->area(),
                'price_per_meter' => $service->price_per_meter,
                'urgency' => $request->urgency()->value,
                'scheduled_at' => $request->scheduledAt(),
                'has_cleaning_material' => $request->hasCleaningMaterials(),
                'location' => $request->location(),
                'lat' => $request->lat(),
                'lng' => $request->lng(),
                'amount' => $breakdown->totalAmount,
                'currency' => $breakdown->currency,
                'status' => BookingStatus::Pending,
                'payment_method' => PaymentMethod::Cod,
            ]);

            foreach ($extras as $extra) {
                $booking
                    ->extras()
                    ->attach($extra->id, [
                        'amount' => $extra->amount,
                        'currency' => $extra->currency,
                    ]);
            }

            foreach ($request->images() as $image) {
                $booking->addMedia($image)->toMediaCollection(Booking::MEDIA_COLLECTION_IMAGES);
            }

            $booking->load(['client', 'cleaner', 'service', 'pricing', 'extras', 'promocode']);

            return BookingResource::make($booking);
        });
    }
}
