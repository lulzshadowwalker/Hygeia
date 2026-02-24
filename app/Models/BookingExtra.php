<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BookingExtra extends Pivot
{
    protected $table = 'booking_extra';

    protected $fillable = [
        'booking_id',
        'extra_id',
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
}
