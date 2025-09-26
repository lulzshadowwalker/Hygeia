<?php

namespace App\Http\Resources\V1;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CleanerDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $averageRating = $this->reviews()->avg('rating');

        return [
            'type' => 'cleaner-dashboard',
            'id' => (string) $this->id,
            'attributes' => [
                'completedBookingsCount' => $this->bookings()->completed()->count(),
                'upcomingBookingsCount' => $this->bookings()->upcoming()->count(),
                'averageRating' => $averageRating ? round($averageRating, 2) : null,
                'totalReviews' => $this->reviews()->count(),
                'earnings' => '4.5',
                'availableDays' => $this->available_days,
            ],
            'includes' => [
                'upcomingBookings' => BookingResource::collection($this->bookings()->upcoming()->limit(2)->get()),
                'offers' => OfferResource::collection(Booking::pending()->limit(2)->get()),
            ],
        ];
    }
}
