<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateCleanerProfileRequest;
use App\Http\Requests\V1\UpdateClientProfileRequest;
use App\Http\Resources\V1\CleanerResource;
use App\Http\Resources\V1\ClientResource;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        switch (true) {
            case $user->isClient:
                return ClientResource::make($user);
            case $user->isCleaner:
                return CleanerResource::make($user->cleaner);
            default:
                throw new Exception('User type not recognized');
        }
    }

    public function update()
    {
        $user = Auth::user();

        switch (true) {
            case $user->isClient:
                return $this->updateClientProfile(
                    app(UpdateClientProfileRequest::class),
                    $user
                );
            case $user->isCleaner:
                return $this->updateCleanerProfile(
                    app(UpdateCleanerProfileRequest::class),
                    $user
                );
            default:
                throw new Exception('User type not recognized');
        }
    }

    private function updateClientProfile(UpdateClientProfileRequest $request, User $user)
    {
        return DB::transaction(function () use ($request, $user) {
            $updateData = [];

            if ($request->name() !== null) {
                $updateData['name'] = $request->name();
            }

            if ($request->phone() !== null) {
                $updateData['phone'] = $request->phone();
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }

            if ($request->avatar()) {
                $user->clearMediaCollection(User::MEDIA_COLLECTION_AVATAR);
                $user->addMedia($request->avatar())
                    ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            }

            return ClientResource::make($user->fresh());
        });
    }

    private function updateCleanerProfile(UpdateCleanerProfileRequest $request, User $user)
    {
        return DB::transaction(function () use ($request, $user) {
            // Update User fields
            $userUpdateData = [];

            if ($request->name() !== null) {
                $userUpdateData['name'] = $request->name();
            }

            if ($request->phone() !== null) {
                $userUpdateData['phone'] = $request->phone();
            }

            if (! empty($userUpdateData)) {
                $user->update($userUpdateData);
            }

            // Update avatar if provided
            if ($request->avatar()) {
                $user->clearMediaCollection(User::MEDIA_COLLECTION_AVATAR);
                $user->addMedia($request->avatar())
                    ->toMediaCollection(User::MEDIA_COLLECTION_AVATAR);
            }

            // Update Cleaner-specific fields
            $cleanerUpdateData = [];

            if ($request->yearsOfExperience() !== null) {
                $cleanerUpdateData['years_of_experience'] = $request->yearsOfExperience();
            }

            if ($request->maxHoursPerWeek() !== null) {
                $cleanerUpdateData['max_hours_per_week'] = $request->maxHoursPerWeek();
            }

            if ($request->serviceRadius() !== null) {
                $cleanerUpdateData['service_radius'] = $request->serviceRadius();
            }

            if ($request->availableDays() !== null) {
                $cleanerUpdateData['available_days'] = $request->availableDays();
            }

            if ($request->timeSlots() !== null) {
                $cleanerUpdateData['time_slots'] = $request->timeSlots();
            }

            if ($request->hasCleaningSupplies() !== null) {
                $cleanerUpdateData['has_cleaning_supplies'] = $request->hasCleaningSupplies();
            }

            if ($request->comfortableWithPets() !== null) {
                $cleanerUpdateData['comfortable_with_pets'] = $request->comfortableWithPets();
            }

            if (! empty($cleanerUpdateData)) {
                $user->cleaner->update($cleanerUpdateData);
            }

            return CleanerResource::make($user->cleaner->fresh());
        });
    }
}
