<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'previous_job_types',
        'service_radius',
        'preferred_job_types',
        'agreed_to_terms',
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

    public function registerMediaConversions(?Media $media = null): void
    {
        $name = Str::replace(" ", "+", $this->fullName);

        $this->registerMediaCollection(self::MEDIA_COLLECTION_ID_CARD)
            ->singleFile()
            ->useFallbackUrl("https://ui-avatars.com/api/?name={$name}");
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
}
