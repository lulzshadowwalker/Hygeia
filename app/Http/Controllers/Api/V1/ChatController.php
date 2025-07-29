<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function getReverbConfig(Request $request): JsonResponse
    {
        return response()->json([
            'reverb' => [
                'key' => config('reverb.key'),
                'host' => config('reverb.host'),
                'port' => config('reverb.port'),
                'scheme' => config('reverb.scheme'),
            ]
        ]);
    }
}
