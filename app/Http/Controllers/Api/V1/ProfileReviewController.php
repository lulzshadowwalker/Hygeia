<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReviewResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;

#[Group('User Profile')]
class ProfileReviewController extends Controller
{
    /**
     * List reviews for the authenticated cleaner
     *
     * Get a list of all reviews for the authenticated cleaner.
     */
    public function index()
    {
        $reviews = Auth::user()->cleaner->reviews;

        return ReviewResource::collection($reviews);
    }
}
