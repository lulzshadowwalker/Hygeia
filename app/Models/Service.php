<?php

namespace App\Models;

use App\Enums\ServiceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected function casts(): array
    {
        return [
            'type' => ServiceType::class,

            //  TODO: Add a money cast
            'price_per_meter' => 'decimal:2',
        ];
    }

    public function pricings()
    {
        return $this->hasMany(Pricing::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // cleaners who have previously used this service
    public function previousCleaners()
    {
        return $this->belongsToMany(
            Cleaner::class,
            'previous_service_cleaner',
            'service_id',
            'cleaner_id'
        )->withTimestamps();
    }

    public function preferredByCleaners()
    {
        return $this->belongsToMany(
            Cleaner::class,
            'preferred_service_cleaner',
            'service_id',
            'cleaner_id'
        )->withTimestamps();
    }
}
