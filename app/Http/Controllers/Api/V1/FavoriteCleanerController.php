<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CleanerResource;
use App\Models\Cleaner;
use Dedoc\Scramble\Attributes\Group;

#[Group('Cleaners')]
class FavoriteCleanerController extends Controller
{
    /**
     * List favorite cleaners
     *
     * List all favorite cleaners for the authenticated client.
     */
    public function index()
    {
        return CleanerResource::collection(
            auth()->user()->client->favoriteCleaners
        );
    }

    /**
     * Add a cleaner to favorites
     *
     * Add a specific cleaner to the authenticated user's favorites.
     */
    public function store(Cleaner $cleaner)
    {
        //  TODO: It would be better if we simply used a client_favorites table with
        //  a polymorphic relation to Cleaner, so that we can favorite any model.
        auth()->user()->client->favoriteCleaners()
            ->syncWithoutDetaching([$cleaner->id]);

        return response()->noContent(204);
    }

    /**
     * Remove a cleaner from favorites
     *
     * Remove a specific cleaner from the authenticated user's favorites.
     */
    public function destroy(Cleaner $cleaner)
    {
        auth()->user()->client->favoriteCleaners()
            ->detach($cleaner->id);

        return response()->noContent(204);
    }
}
