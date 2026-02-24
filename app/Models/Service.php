<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Service extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceFactory> */
    use HasFactory, HasTranslations;

    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'type',
        'price_per_meter',
        'currency',
    ];

    protected $attributes = [
        'currency' => 'HUF',
    ];

    protected function casts(): array
    {
        return [
            'type' => ServiceType::class,
            'price_per_meter' => MoneyCast::class,
            'currency' => 'string',
        ];
    }

    public function pricings(): HasMany
    {
        return $this->hasMany(Pricing::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function previousCleaners(): BelongsToMany
    {
        return $this->belongsToMany(
            Cleaner::class,
            'previous_service_cleaner',
            'service_id',
            'cleaner_id'
        )->withTimestamps();
    }

    public function preferredByCleaners(): BelongsToMany
    {
        return $this->belongsToMany(
            Cleaner::class,
            'preferred_service_cleaner',
            'service_id',
            'cleaner_id'
        )->withTimestamps();
    }
}
