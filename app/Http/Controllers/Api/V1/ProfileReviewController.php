<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReviewResource;
use Illuminate\Support\Facades\Auth;

class ProfileReviewController extends Controller
{
    public function index()
    {
        $reviews = Auth::user()->cleaner->reviews;

        return ReviewResource::collection($reviews);
    }
}
