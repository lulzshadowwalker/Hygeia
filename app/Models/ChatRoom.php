<?php

namespace App\Models;

use App\Enums\ChatRoomType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatRoom extends Model
{
    /** @use HasFactory<\Database\Factories\ChatRoomFactory> */
    use HasFactory;

    protected $fillable = ['type'];

    protected function casts(): array
    {
        return [
            'type' => ChatRoomType::class,
        ];
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_room_participants')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latest();
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

    // Scopes
    public function scopeSupport($query)
    {
        return $query->where('type', ChatRoomType::Support);
    }

    // Accessors & Mutators
    protected function messagesCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->messages()->count(),
        );
    }
}
