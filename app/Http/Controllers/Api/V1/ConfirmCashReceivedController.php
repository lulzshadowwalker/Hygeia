<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\BookingResource;
use App\Models\Booking;
use App\Services\Payments\CodSettlementService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

#[Group('Bookings')]
class ConfirmCashReceivedController extends ApiController
{
    /**
     * Confirm cash was received for a booking.
     */
    public function store(Booking $booking, CodSettlementService $codSettlementService): mixed
    {
        $this->authorize('confirmCashReceived', $booking);

        return DB::transaction(function () use ($booking, $codSettlementService) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->whereKey($booking->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->cash_received_at !== null) {
                return $this->response->error(
                    title: 'Cash Already Confirmed',
                    detail: 'Cash has already been confirmed for this booking.',
                    code: Response::HTTP_CONFLICT
                )->build(Response::HTTP_CONFLICT);
            }

            if (! $lockedBooking->status->isConfirmed()) {
                return $this->response->error(
                    title: 'Invalid Booking Status',
                    detail: 'Only confirmed bookings can receive cash confirmation.',
                    code: Response::HTTP_BAD_REQUEST
                )->build(Response::HTTP_BAD_REQUEST);
            }

            $cleaner = auth()->user()->cleaner;

            $result = $codSettlementService->settle($lockedBooking, $cleaner);
            $lockedBooking->cash_received_at = now();
            $lockedBooking->cash_received_amount = $result['payout'];
            $lockedBooking->cash_received_currency = $lockedBooking->currency;
            $lockedBooking->cash_received_wallet_transaction_id = $result['transaction']->getKey();
            $lockedBooking->save();

            return BookingResource::make($lockedBooking->fresh());
        });
    }
}
