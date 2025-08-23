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
    ];

    protected function casts(): array
    {
        return [
            'type' => ServiceType::class,
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
}
