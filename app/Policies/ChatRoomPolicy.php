<?php

namespace App\Policies;

use App\Models\ChatRoom;
use App\Models\User;
use App\Enums\ChatRoomType;
use Illuminate\Auth\Access\Response;

class ChatRoomPolicy
{
    /**
     * Determine whether the user can view any chat rooms.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view their own chat rooms
    }

    /**
     * Determine whether the user can view the chat room.
     */
    public function view(User $user, ChatRoom $chatRoom): Response
    {
        return $chatRoom->isParticipant($user)
            ? Response::allow()
            : Response::deny('You are not a participant of this chat room.');
    }

    /**
     * Determine whether the user can create chat rooms.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create chat rooms
    }

    /**
     * Determine whether the user can update the chat room.
     */
    public function update(User $user, ChatRoom $chatRoom): bool
    {
        // Only admins or room creators can update
        return $user->isAdmin || $chatRoom->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the chat room.
     */
    public function delete(User $user, ChatRoom $chatRoom): bool
    {
        // Only admins or room creators can delete
        return $user->isAdmin || $chatRoom->created_by === $user->id;
    }

    /**
     * Determine whether the user can join the chat room.
     */
    public function join(User $user, ChatRoom $chatRoom): bool
    {
        // Support rooms: anyone can join (even if already a participant - handled in controller)
        if ($chatRoom->type === ChatRoomType::Support) {
            return true;
        }

        // For other room types, implement your business logic
        // For now, allow joining any room (even if already a participant - handled in controller)
        return true;
    }

    /**
     * Determine whether the user can leave the chat room.
     */
    public function leave(User $user, ChatRoom $chatRoom): bool
    {
        // Allow all users to attempt to leave (even if not participants - handled in controller)
        return true;
    }

    /**
     * Determine whether the user can access support chat rooms.
     */
    public function accessSupport(User $user): bool
    {
        // All authenticated users can access support
        return true;
    }

    /**
     * Determine whether the user can moderate the chat room.
     */
    public function moderate(User $user, ChatRoom $chatRoom): bool
    {
        // Only admins or users with admin role in the chat room
        return $user->isAdmin || 
               $chatRoom->participants()
                        ->where('user_id', $user->id)
                        ->where('role', 'admin')
                        ->exists();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ChatRoom $chatRoom): bool
    {
        return $user->isAdmin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ChatRoom $chatRoom): bool
    {
        return $user->isAdmin;
    }
}
