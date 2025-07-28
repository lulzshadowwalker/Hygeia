<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatRoom extends Model
{
    /** @use HasFactory<\Database\Factories\ChatRoomFactory> */
    use HasFactory;

    protected $fillable = [];

    public function name(): Attribute
    {
        return Attribute::get(function (): string {
            //  TODO: Localization
            return 'Support';
        });
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_participants')
            ->withPivot(['last_seen_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    public function getChannelName(): string
    {
        return "chat.room.{$this->id}";
    }

    public function isParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    //  TODO: Refactor this to use an Action class instead
    public function addParticipant(User $user): void
    {
        $this->participants()->attach($user->id);
    }

    //  TODO: Refactor this to use an Action class instead
    public function removeParticipant(User $user): void
    {
        $this->participants()->detach($user->id);
    }
}
