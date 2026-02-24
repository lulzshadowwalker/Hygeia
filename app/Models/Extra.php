<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Pivots\BookingExtra;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Extra extends Model
{
    /** @use HasFactory<\Database\Factories\ExtraFactory> */
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'amount',
        'currency',
    ];

    protected $attributes = [
        'currency' => 'HUF',
    ];

    protected function casts(): array
    {
        return [
            'amount' => MoneyCast::class,
            'currency' => 'string',
        ];
    }

    public array $translatable = [
        'name',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class)
            ->using(BookingExtra::class)
            ->withPivot(['amount', 'currency'])
            ->withTimestamps();
    }
}
