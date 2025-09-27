<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ExtraResource;
use App\Models\Extra;
use Dedoc\Scramble\Attributes\Group;

#[Group('Services')]
class ExtraController extends Controller
{
    /**
     * List extras
     *
     * Get a list of all extras.
     *
     * @unauthenticated
     */
    public function index()
    {
        $extras = Extra::all();

        return ExtraResource::collection($extras);
    }

    /**
     * Get an extra
     *
     * Get the details of a specific extra.
     *
     * @unauthenticated
     */
    public function show(Extra $extra)
    {
        return ExtraResource::make($extra);
    }
}
