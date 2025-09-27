<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DistrictResource;
use App\Models\District;
use Dedoc\Scramble\Attributes\Group;

#[Group('Districts')]
class DistrictController extends Controller
{
    /**
     * List districts
     *
     * Get a list of all districts.
     *
     * @unauthenticated
     */
    public function index()
    {
        $districts = District::all();

        return DistrictResource::collection($districts);
    }

    /**
     * Get a district
     *
     * Get the details of a specific district.
     *
     * @unauthenticated
     */
    public function show(District $district)
    {
        return DistrictResource::make($district);
    }
}
