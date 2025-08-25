<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CallbackRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\CallbackRequest;

class CallbackRequestController extends Controller
{
    public function store()
    {
        $exists = CallbackRequest::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subMinutes(10))
            ->where('status', CallbackRequestStatus::Pending->value)
            ->exists();

        if ($exists) {
            return response()->noContent(200);
        }

        auth()->user()->callbackRequests()->create();

        return response()->noContent(201);
    }
}
