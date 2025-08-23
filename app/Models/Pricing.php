<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pricing extends Model
{
    /** @use HasFactory<\Database\Factories\PricingFactory> */
    use HasFactory;

    protected $fillable = [
        'min_area',
        'max_area',
        'amount',
        'service_id',
    ];

    protected function casts(): array
    {
        return [
            'min_area' => 'integer',
            'max_area' => 'integer',

            // TODO: Use a money cast
            'amount' => 'decimal:2',
        ];
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
