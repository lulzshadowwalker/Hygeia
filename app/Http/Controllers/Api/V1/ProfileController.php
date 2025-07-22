<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CleanerResource;
use App\Http\Resources\V1\ClientResource;
use Exception;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        switch (true) {
            case $user->isClient:
                return ClientResource::make($user->client);
            case $user->isCleaner:
                return CleanerResource::make($user->cleaner);
            default:
                throw new Exception('User type not recognized');
        }
    }
}
