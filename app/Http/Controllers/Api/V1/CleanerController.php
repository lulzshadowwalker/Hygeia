<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CleanerResource;
use App\Models\Cleaner;
use Dedoc\Scramble\Attributes\Group;

#[Group('Cleaners')]
class CleanerController extends Controller
{
    /**
     * List cleaners
     *
     * Get a list of all cleaners.
     *
     * @unauthenticated
     */
    public function index()
    {
        return CleanerResource::collection(Cleaner::all());
    }

    /**
     * Get a cleaner
     *
     * Get the details of a specific cleaner.
     *
     * @unauthenticated
     */
    public function show(Cleaner $cleaner)
    {
        return CleanerResource::make($cleaner);
    }
}
