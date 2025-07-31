<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view any messages.
     */
    public function viewAny(User $user): bool
    {
        return true; // Users can view messages in their chat rooms
    }

    /**
     * Determine whether the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        // User must be a participant in the chat room to view the message
        return $message->chatRoom->isParticipant($user);
    }

    /**
     * Determine whether the user can create messages.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create messages
    }

    /**
     * Determine whether the user can update the message.
     */
    public function update(User $user, Message $message): bool
    {
        // Only the message author can update their own message
        return $message->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        // Message author, room creator, or admin can delete
        return $message->user_id === $user->id || 
               $message->chatRoom->created_by === $user->id ||
               $user->isAdmin;
    }

    /**
     * Determine whether the user can restore the message.
     */
    public function restore(User $user, Message $message): bool
    {
        // Only admins can restore messages
        return $user->isAdmin;
    }

    /**
     * Determine whether the user can permanently delete the message.
     */
    public function forceDelete(User $user, Message $message): bool
    {
        // Only admins can permanently delete messages
        return $user->isAdmin;
    }

    /**
     * Determine whether the user can react to the message.
     */
    public function react(User $user, Message $message): bool
    {
        // User must be a participant in the chat room to react
        return $message->chatRoom->isParticipant($user);
    }

    /**
     * Determine whether the user can edit the message.
     */
    public function edit(User $user, Message $message): bool
    {
        // Only the message author can edit their own message
        return $message->user_id === $user->id;
    }

    /**
     * Determine whether the user can send messages to a chat room.
     * This method handles authorization for creating messages in a specific chat room.
     */
    public function sendMessage(User $user, $chatRoom): bool
    {
        return $chatRoom->isParticipant($user);
    }

    /**
     * Determine whether the user can view messages in a chat room.
     * This method handles authorization for retrieving messages from a specific chat room.
     */
    public function viewMessages(User $user, $chatRoom): bool
    {
        return $chatRoom->isParticipant($user);
    }
}
