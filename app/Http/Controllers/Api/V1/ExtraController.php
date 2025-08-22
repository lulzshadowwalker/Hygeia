<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ExtraResource;
use App\Models\Extra;

class ExtraController extends Controller
{
    public function index()
    {
        $extras = Extra::all();
        return ExtraResource::collection($extras);
    }

    public function show(Extra $extra)
    {
        return ExtraResource::make($extra);
    }
}
