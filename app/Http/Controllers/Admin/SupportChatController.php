<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChatRoomType;
use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MessageResource;
use App\Models\ChatRoom;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportChatController extends Controller
{
    public function index()
    {
        // Check admin access
        if (!Auth::user()->isAdmin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        $supportRooms = ChatRoom::where('type', ChatRoomType::Support)
            ->with(['participants', 'latestMessage.user'])
            ->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.support.chat.index', compact('supportRooms'));
    }

    public function show(ChatRoom $chatRoom)
    {
        // Check admin access
        if (!Auth::user()->isAdmin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Ensure this is a support room
        if ($chatRoom->type !== ChatRoomType::Support) {
            abort(404);
        }

        // Add admin to room if not already a participant
        if (!$chatRoom->isParticipant(Auth::user())) {
            $chatRoom->addParticipant(Auth::user());
        }

        $chatRoom->load(['participants']);
        
        // Get messages for the view (oldest first for proper chat order)
        $messages = Message::where('chat_room_id', $chatRoom->id)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Get Reverb configuration for real-time features
        $reverbConfig = [
            'key' => config('broadcasting.connections.reverb.key'),
            'host' => config('broadcasting.connections.reverb.options.host'),
            'port' => config('broadcasting.connections.reverb.options.port'),
            'scheme' => config('broadcasting.connections.reverb.options.scheme'),
        ];

        return view('admin.support.chat.show', compact('chatRoom', 'messages', 'reverbConfig'));
    }

    public function sendMessage(Request $request, ChatRoom $chatRoom)
    {
        // Check admin access
        if (!Auth::user()->isAdmin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        // Ensure this is a support room and admin is a participant
        if ($chatRoom->type !== ChatRoomType::Support || !$chatRoom->isParticipant(Auth::user())) {
            return response()->json(['error' => 'Access denied to this chat room'], 403);
        }

        $message = Message::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => Auth::id(),
            'content' => trim($request->input('content')),
            'type' => MessageType::Text,
        ]);

        $message->load('user');

        // Broadcast the message
        MessageSent::dispatch($message);

        return response()->json([
            'success' => true,
            'data' => MessageResource::make($message),
        ]);
    }

    public function getMessages(ChatRoom $chatRoom)
    {
        // Check admin access
        if (!Auth::user()->isAdmin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        if ($chatRoom->type !== ChatRoomType::Support || !$chatRoom->isParticipant(Auth::user())) {
            return response()->json(['error' => 'Access denied to this chat room'], 403);
        }

        $messages = $chatRoom->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'data' => MessageResource::collection($messages),
        ]);
    }

    public function getReverbConfig()
    {
        // Check admin access
        if (!Auth::user()->isAdmin) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json([
            'key' => config('broadcasting.connections.reverb.key'),
            'host' => config('broadcasting.connections.reverb.options.host'),
            'port' => config('broadcasting.connections.reverb.options.port'),
            'scheme' => config('broadcasting.connections.reverb.options.scheme'),
        ]);
    }
}
