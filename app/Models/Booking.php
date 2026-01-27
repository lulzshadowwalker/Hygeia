<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookingUrgency;
use App\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Booking extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'client_id',
        'service_id',
        'pricing_id',
        'selected_amount',
        'urgency',
        'scheduled_at',
        'has_cleaning_material',
        'amount',
        'status',
        'location',
        'lat',
        'lng',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'urgency' => BookingUrgency::class,
            'scheduled_at' => 'datetime',
            'has_cleaning_material' => 'boolean',
            'location' => 'string',
            'lat' => 'decimal:7',
            'lng' => 'decimal:7',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function pricing(): BelongsTo
    {
        return $this->belongsTo(Pricing::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class);
    }

    public function cleaner(): BelongsTo
    {
        return $this->belongsTo(Cleaner::class);
    }

    public function scopeFilter(Builder $builder, QueryFilter $filters): Builder
    {
        return $filters->apply($builder);
    }

    public function scopePending(Builder $builder): Builder
    {
        return $builder->where('status', BookingStatus::Pending);
    }

    public function scopeCompleted(Builder $builder): Builder
    {
        return $builder->where('status', BookingStatus::Completed);
    }

    public function scopeCancelled(Builder $builder): Builder
    {
        return $builder->where('status', BookingStatus::Cancelled);
    }

    public function scopeConfirmed(Builder $builder): Builder
    {
        return $builder->where('status', BookingStatus::Confirmed);
    }

    public function scopeUpcoming(Builder $builder): Builder
    {
        return $builder
            ->whereIn('status', [
                BookingStatus::Pending,
                BookingStatus::Confirmed,
            ])
            ->where('scheduled_at', '>=', now());
    }

    public function chatRooms(): HasMany
    {
        return $this->hasMany(ChatRoom::class);
    }

    const MEDIA_COLLECTION_IMAGES = 'images';

    public function images(): Attribute
    {
        return Attribute::get(function () {
            return $this->getMedia(self::MEDIA_COLLECTION_IMAGES)->map(function ($media) {
                return $media->getUrl();
            })->toArray();
        });
    }
}
