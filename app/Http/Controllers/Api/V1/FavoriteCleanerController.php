<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cleaner;

class FavoriteCleanerController extends Controller
{
    public function store(Cleaner $cleaner)
    {
        //  TODO: It would be better if we simply used a client_favorites table with
        //  a polymorphic relation to Cleaner, so that we can favorite any model.
        auth()->user()->client->favoriteCleaners()
            ->syncWithoutDetaching([$cleaner->id]);

        return response()->noContent(204);
    }

    public function destroy(Cleaner $cleaner)
    {
        auth()->user()->client->favoriteCleaners()
            ->detach($cleaner->id);

        return response()->noContent(204);
    }
}
