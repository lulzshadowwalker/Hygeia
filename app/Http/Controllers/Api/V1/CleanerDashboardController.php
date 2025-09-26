<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CleanerDashboardResource;
use Illuminate\Support\Facades\Auth;

class CleanerDashboardController extends Controller
{
    public function index()
    {
        $cleaner = Auth::user()->cleaner;

        return CleanerDashboardResource::make($cleaner);
    }
}
