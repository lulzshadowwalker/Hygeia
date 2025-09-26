<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;

class Cleaner extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_area',
        'available_days',
        'max_hours_per_week',
        'time_slots',
        'years_of_experience',
        'has_cleaning_supplies',
        'comfortable_with_pets',
        'service_radius',
        'agreed_to_terms',
        'accepts_urgent_offers',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'available_days' => 'array',
            'time_slots' => 'array',
            'has_cleaning_supplies' => 'boolean',
            'comfortable_with_pets' => 'boolean',
            'accepts_urgent_offers' => 'boolean',
            'previous_job_types' => 'array',
            'preferred_job_types' => 'array',
            'agreed_to_terms' => 'boolean',
            'user_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    const MEDIA_COLLECTION_ID_CARD = 'id-card';

    public function registerMediaCollection(): void
    {
        $name = Str::replace(" ", "+", $this->fullName);

        $this->addMediaCollection(self::MEDIA_COLLECTION_ID_CARD)
            ->singleFile()
            ->useFallbackUrl("https://ui-avatars.com/api/?name={$name}")

            //  TODO: Media conversions need to be tested
            ->registerMediaConversions(function (Media $media = null) {
                $this->addMediaConversion('thumb')
                    ->width(100)
                    ->sharpen(10);

                $this->addMediaConversion('preview')
                    ->width(300)
                    ->sharpen(10);
            });
        // ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Get the cleaner's id card URL.
     */
    public function idCard(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMediaUrl(self::MEDIA_COLLECTION_ID_CARD) ?:
                null
        );
    }

    /**
     * Get the cleaner's id card file.
     */
    public function idCardFile(): Attribute
    {
        return Attribute::get(
            fn() => $this->getFirstMedia(self::MEDIA_COLLECTION_ID_CARD) ?: null
        );
    }

    /**
     * Get the clients who have favorited this cleaner.
     */
    //  TODO: This should be depracated in favor of the `favorites` method.
    public function favoriteClients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_favorite_cleaners')
            ->withTimestamps();
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function reviews(): morphMany
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function serviceArea(): BelongsTo
    {
        return $this->belongsTo(District::class, 'service_area');
    }

    // services the cleaner has previously used (filled only on registeration)
    public function previousServices()
    {
        return $this->belongsToMany(
            Service::class,
            'previous_service_cleaner',
            'cleaner_id',
            'service_id'
        )->withTimestamps();
    }

    public function preferredServices()
    {
        return $this->belongsToMany(
            Service::class,
            'preferred_service_cleaner',
            'cleaner_id',
            'service_id'
        )->withTimestamps();
    }

    public function bookings(): HasMany 
    {
        return $this->hasMany(Booking::class);
    }
}
