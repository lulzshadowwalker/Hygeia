<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CleanerDashboardResource;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Auth;

#[Group('User Profile')]
class CleanerDashboardController extends Controller
{
    /**
     * Get cleaner dashboard
     *
     * Get dashboard data for the authenticated cleaner.
     */
    public function index()
    {
        $cleaner = Auth::user()->cleaner;

        return CleanerDashboardResource::make($cleaner);
    }
}
