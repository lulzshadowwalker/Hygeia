<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Enums\ServicePricingModel;
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
        'pricing_model',
        'price_per_meter',
        'min_area',
        'currency',
    ];

    protected $attributes = [
        'currency' => 'HUF',
        'pricing_model' => ServicePricingModel::AreaRange->value,
    ];

    protected function casts(): array
    {
        return [
            'type' => ServiceType::class,
            'pricing_model' => ServicePricingModel::class,
            'price_per_meter' => MoneyCast::class,
            'min_area' => 'integer',
            'currency' => 'string',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            if ($service->type === ServiceType::Residential) {
                $service->pricing_model = ServicePricingModel::AreaRange;
                $service->price_per_meter = null;
                $service->min_area = null;
            }
        });
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

    public function effectivePricingModel(): ServicePricingModel
    {
        if ($this->type === ServiceType::Residential) {
            return ServicePricingModel::AreaRange;
        }

        return $this->pricing_model ?? ServicePricingModel::AreaRange;
    }

    public function usesAreaRangePricing(): bool
    {
        return $this->effectivePricingModel() === ServicePricingModel::AreaRange;
    }

    public function usesPricePerMeterPricing(): bool
    {
        return $this->effectivePricingModel() === ServicePricingModel::PricePerMeter;
    }
}
