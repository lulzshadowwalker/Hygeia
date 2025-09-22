<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Notifications\CustomResetPasswordNotification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, InteractsWithMedia, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'phone',
        'name',
        'username',
        'email',
        'password',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
        ];
    }

    public function isClient(): Attribute
    {
        return Attribute::get(fn(): bool => $this->hasRole(Role::Client->value));
    }

    public function isCleaner(): Attribute
    {
        return Attribute::get(fn(): bool => $this->hasRole(Role::Cleaner->value));
    }

    public function isAdmin(): Attribute
    {
        return Attribute::get(fn(): bool => $this->hasRole(Role::Admin->value));
    }

    public function scopeClients(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', Role::Client->value);
        });
    }

    public function scopeCleaners(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', Role::Cleaner->value);
        });
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->whereHas('roles', function (Builder $query) {
            $query->where('name', Role::Admin->value);
        });
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class);
    }

    public function cleaner(): HasOne
    {
        return $this->hasOne(Cleaner::class);
    }

    public function preferences(): HasOne
    {
        return $this->hasOne(UserPreferences::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    const MEDIA_COLLECTION_AVATAR = 'avatar';

    public function registerMediaCollections(): void
    {
        $name = Str::replace(" ", "+", $this->name);

        $this->addMediaCollection(self::MEDIA_COLLECTION_AVATAR)
            ->singleFile()
            ->useFallbackUrl("https://ui-avatars.com/api/?name={$name}");
    }

    /**
     * Get the user's avatar URL.
     */
    public function avatar(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_AVATAR) ?:
                null
        );
    }

    /**
     * Get the user's avatar file.
     */
    public function avatarFile(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMedia(self::MEDIA_COLLECTION_AVATAR) ?: null
        );
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function routeNotificationForPush(Notification $notification): array
    {
        return $this->deviceTokens->pluck("token")->toArray();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin;
    }

    //  NOTE: this method is unused, but notice how nicely it is implemented ..
    // public function createdChatRooms(): HasMany
    // {
    //     return $this->hasMany(ChatRoom::class, 'created_by');
    // }

    public function chatRooms(): BelongsToMany
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_participants')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function callbackRequests(): HasMany
    {
        return $this->hasMany(CallbackRequest::class);
    }

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }
}
