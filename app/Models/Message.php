<?php

namespace App\Models;

use App\Enums\MessageType;
use App\Observers\MessageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

#[ObservedBy(MessageObserver::class)]
class Message extends Model
{
    /** @use HasFactory<\Database\Factories\MessageFactory> */
    use HasFactory;

    protected $touches = ['chatRoom'];

    protected $fillable = [
        'chat_room_id',
        'user_id',
        'content',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => MessageType::class,
        ];
    }

    public function chatRoom(): BelongsTo
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mine(): Attribute
    {
        return Attribute::get(fn (): bool => $this->user_id === Auth::id());
    }
}
