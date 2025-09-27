<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ReverbConfig;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Chat')]
class ChatController extends Controller
{
    /**
     * Get Reverb configuration
     *
     * Get the configuration needed to connect to the Reverb WebSocket server.
     */
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
