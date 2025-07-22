<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CleanerResource;
use App\Models\Cleaner;

class CleanerController extends Controller
{
    public function index()
    {
        return CleanerResource::collection(Cleaner::all());
    }

    public function show(Cleaner $cleaner)
    {
        return CleanerResource::make($cleaner);
    }
}
