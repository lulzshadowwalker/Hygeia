<?php

namespace App\Models;

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
    ];

    protected $casts = [
        //  TODO: Use a money cast
        'amount' => 'decimal:2',
    ];

    public array $translatable = [
        'name',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class);
    }
}
