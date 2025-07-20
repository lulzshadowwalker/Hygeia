<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cleaner;

class FavoriteCleanerController extends Controller
{
    public function store(Cleaner $cleaner)
    {
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
