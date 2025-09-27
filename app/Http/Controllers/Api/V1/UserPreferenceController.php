<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateUserPreferenceRequest;
use App\Http\Resources\V1\UserPreferenceResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;

#[Group('User Profile')]
class UserPreferenceController extends Controller
{
    /**
     * Get user preferences
     *
     * Get the preferences of the currently authenticated user.
     */
    public function index()
    {
        $preferences = Auth::user()->preferences()->firstOrCreate([
            'user_id' => Auth::user()->id,
        ]);

        return UserPreferenceResource::make($preferences->fresh());
    }

    /**
     * Update user preferences
     *
     * Update the preferences of the currently authenticated user.
     */
    public function update(UpdateUserPreferenceRequest $request)
    {
        $preferences = Auth::user()->preferences()->updateOrCreate(
            ['user_id' => Auth::user()->id],
            $request->mappedAttributes()->toArray(),
        );

        return UserPreferenceResource::make($preferences);
    }
}
