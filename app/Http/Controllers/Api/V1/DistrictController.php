<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DistrictResource;
use App\Models\District;

class DistrictController extends Controller
{
    public function index()
    {
        $districts = District::all();

        return DistrictResource::collection($districts);
    }

    public function show(District $district)
    {
        return DistrictResource::make($district);
    }
}
