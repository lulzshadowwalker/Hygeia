<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRegisterCleanerRequest;
use App\Http\Resources\V1\AuthTokenResource;
use App\Models\User;
use App\Support\AccessToken;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\DB;

#[Group('Authentication')]
class RegisterCleanerController extends Controller
{
    /**
     * Register a new cleaner
     *
     * Handle new cleaner registration and issue an authentication token.
     */
    public function store(StoreRegisterCleanerRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name(),
                'phone' => $request->phone(),
                'username' => $request->username(),
                'email' => $request->email(),
                'password' => $request->password(),
            ]);

            if ($request->avatar()) {
                $user->addMedia($request->avatar())
                    ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            }

            $cleaner = $user->cleaner()->create([
                'available_days' => $request->availableDays(),
                'time_slots' => $request->timeSlots(),
                'max_hours_per_week' => $request->maxHoursPerWeek(),
                'accepts_urgent_offers' => $request->acceptsUrgentOffers(),
                'years_of_experience' => $request->yearsOfExperience(),
                'has_cleaning_supplies' => $request->hasCleaningSupplies(),
                'comfortable_with_pets' => $request->comfortableWithPets(),
                'service_radius' => $request->serviceRadius(),
                'agreed_to_terms' => $request->agreedToTerms(),
            ]);
            if ($request->idCard()) {
                $cleaner->addMedia($request->idCard())
                    ->toMediaCollection($cleaner::MEDIA_COLLECTION_ID_CARD);
            }

            if ($previousServices = $request->previousServices()) {
                $cleaner->previousServices()->sync($previousServices);
            }

            if ($preferredServices = $request->preferredServices()) {
                $cleaner->preferredServices()->sync($preferredServices);
            }

            if ($deviceToken = $request->deviceToken()) {
                $user->deviceTokens()->firstOrCreate(['token' => $deviceToken]);
            }

            $user->assignRole(Role::Cleaner->value);

            $accessToken = $user->createToken(config('app.name'))->plainTextToken;

            return AuthTokenResource::make(
                new AccessToken(accessToken: $accessToken, role: Role::Cleaner),
            )->response()->setStatusCode(201);
        });
    }
}
