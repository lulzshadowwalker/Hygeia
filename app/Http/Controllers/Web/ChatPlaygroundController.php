<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatPlaygroundController extends Controller
{
    public function index()
    {
        // Get all users for testing
        $users = User::with(['client', 'cleaner'])->get();

        // Get all chat rooms with participants and latest messages
        $chatRooms = ChatRoom::with(['participants', 'latestMessage.user', 'messages.user'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('chat.playground', compact('users', 'chatRooms'));
    }

    public function loginAs(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        Auth::login($user);

        return redirect()->route('chat.playground')->with('success', "Logged in as {$user->name}");
    }
}
