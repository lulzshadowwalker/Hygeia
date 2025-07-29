<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ChatRoomResource;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatRoomController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $chatRooms = $user->chatRooms()->with(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }])->orderBy('updated_at', 'desc')->get();

        return response()->json([
            'data' => ChatRoomResource::collection($chatRooms)
        ]);
    }

    public function show(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        if (!$chatRoom->participants->contains($user)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $chatRoom->load(['participants', 'messages' => function ($query) {
            $query->latest()->limit(1);
        }]);

        return response()->json([
            'data' => new ChatRoomResource($chatRoom)
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'participant_ids' => 'required|array|min:1',
            'participant_ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $participantIds = $request->participant_ids;

        // Add the creator to the participants
        if (!in_array($user->id, $participantIds)) {
            $participantIds[] = $user->id;
        }

        $chatRoom = ChatRoom::create([
            'name' => $request->name,
            'description' => $request->description,
            'created_by' => $user->id,
        ]);

        $chatRoom->participants()->attach($participantIds);
        $chatRoom->load('participants');

        return response()->json([
            'data' => new ChatRoomResource($chatRoom)
        ], 201);
    }


    public function join(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        if ($chatRoom->participants->contains($user)) {
            return response()->json(['message' => 'User is already a participant'], 200);
        }

        $chatRoom->participants()->attach($user->id);
        $chatRoom->load('participants');

        return response()->json([
            'message' => 'Successfully joined chat room',
            'data' => new ChatRoomResource($chatRoom)
        ], 200);
    }

    public function leave(Request $request, ChatRoom $chatRoom)
    {
        $user = $request->user();

        if (!$chatRoom->participants->contains($user)) {
            return response()->json(['message' => 'Successfully left chat room'], 200);
        }

        $chatRoom->participants()->detach($user->id);

        return response()->json(['message' => 'Successfully left chat room'], 200);
    }
}
