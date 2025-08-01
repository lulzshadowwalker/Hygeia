<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChatRoomType;
use App\Enums\MessageType;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\MessageResource;
use App\Models\ChatRoom;
use App\Models\Message;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupportChatController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        // Use policy for authorization
        $this->authorize('viewAny', ChatRoom::class);

        $supportRooms = ChatRoom::where('type', ChatRoomType::Support)
            ->with(['participants', 'latestMessage.user'])
            ->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.support.chat.index', compact('supportRooms'));
    }

    public function show(ChatRoom $chatRoom)
    {
        // Use policy for authorization - this will check if user can view this specific chat room
        $this->authorize('view', $chatRoom);

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
            ->get()
            ->map(fn ($message) => MessageResource::make($message)->toArray(request()));

        $supportRooms = ChatRoom::where('type', ChatRoomType::Support)
            ->with(['participants', 'latestMessage.user'])
            ->withCount('messages')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get Reverb configuration for real-time features
        $reverbConfig = [
            'key' => config('broadcasting.connections.reverb.key'),
            'host' => config('broadcasting.connections.reverb.options.host'),
            'port' => config('broadcasting.connections.reverb.options.port'),
            'scheme' => config('broadcasting.connections.reverb.options.scheme'),
        ];

        return view('admin.support.chat.show', compact('chatRoom', 'messages', 'reverbConfig', 'supportRooms'));
    }

    public function sendMessage(Request $request, ChatRoom $chatRoom)
    {
        // Use policy for authorization - same as API controllers
        $this->authorize('sendMessage', [Message::class, $chatRoom]);

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        // Ensure this is a support room
        if ($chatRoom->type !== ChatRoomType::Support) {
            return response()->json(['error' => 'Invalid chat room type'], 403);
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

        return redirect()->back();
    }

    public function getReverbConfig()
    {
        // Use policy for authorization - ensure admin access
        $this->authorize('viewAny', ChatRoom::class);

        // Additional admin check for config access
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
