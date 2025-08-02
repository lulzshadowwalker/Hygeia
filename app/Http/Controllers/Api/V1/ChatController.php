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
            'key' => config('broadcasting.connections.reverb.key'),
            'host' => config('broadcasting.connections.reverb.options.host'),
            'port' => config('broadcasting.connections.reverb.options.port'),
            'scheme' => config('broadcasting.connections.reverb.options.scheme'),
        ]);
    }
}
