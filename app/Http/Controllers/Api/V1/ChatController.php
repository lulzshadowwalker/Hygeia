<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReverbConfig;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getReverbConfig(Request $request)
    {
        return ReverbConfig::make((object) [
            'key' => config('reverb.key'),
            'host' => config('reverb.host'),
            'port' => config('reverb.port'),
            'scheme' => config('reverb.scheme'),
        ]);
    }
}
