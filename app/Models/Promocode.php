<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Promocode extends Model
{
    /** @use HasFactory<\Database\Factories\PromocodeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'discount_percentage',
        'max_discount_amount',
        'currency',
        'starts_at',
        'expires_at',
        'max_global_uses',
    ];

    protected $attributes = [
        'currency' => 'HUF',
    ];

    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'max_discount_amount' => MoneyCast::class,
            'currency' => 'string',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'max_global_uses' => 'integer',
        ];
    }

    public function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isActiveAt(?Carbon $moment = null): bool
    {
        $moment ??= now();

        if ($this->starts_at !== null && $moment->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at !== null && $moment->gt($this->expires_at)) {
            return false;
        }

        return true;
    }
}
