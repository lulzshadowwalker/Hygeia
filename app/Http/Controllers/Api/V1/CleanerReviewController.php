<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCleanerReviewRequest;
use App\Http\Resources\V1\ReviewResource;
use App\Models\Cleaner;
use App\Models\Review;

class CleanerReviewController extends Controller
{
    public function index(Cleaner $cleaner)
    {
        return ReviewResource::collection($cleaner->reviews);
    }

    public function show(Cleaner $cleaner, Review $review)
    {
        if (! $cleaner->reviews->contains($review)) {
            abort(404, 'Review not found for the specified cleaner.');
        }

        return ReviewResource::make($review);
    }

    public function store(StoreCleanerReviewRequest $request, Cleaner $cleaner)
    {
        $review = $cleaner->reviews()->create([
            ...$request->mappedAttributes(),
            'user_id' => $request->user()->id,
        ]);

        return ReviewResource::make($review);
    }
}
