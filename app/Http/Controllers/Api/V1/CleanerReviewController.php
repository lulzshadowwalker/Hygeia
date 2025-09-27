<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCleanerReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Cleaner;
use App\Models\Review;
use Dedoc\Scramble\Attributes\Group;

#[Group('Cleaners')]
class CleanerReviewController extends Controller
{
    /**
     * List reviews for a cleaner
     *
     * Get a list of all reviews for a specific cleaner.
     */
    public function index(Cleaner $cleaner)
    {
        return ReviewResource::collection($cleaner->reviews);
    }

    /**
     * Get a specific review for a cleaner
     *
     * Get the details of a specific review for a cleaner.
     */
    public function show(Cleaner $cleaner, Review $review)
    {
        if (! $cleaner->reviews->contains($review)) {
            abort(404, 'Review not found for the specified cleaner.');
        }

        return ReviewResource::make($review);
    }

    /**
     * Add a review for a cleaner
     *
     * Add a new review for a specific cleaner.
     */
    public function store(StoreCleanerReviewRequest $request, Cleaner $cleaner)
    {
        $review = $cleaner->reviews()->create([
            ...$request->mappedAttributes(),
            'user_id' => $request->user()->id,
        ]);

        return ReviewResource::make($review);
    }
}
