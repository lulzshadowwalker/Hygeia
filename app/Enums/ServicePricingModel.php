<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ServicePricingModel: string implements HasColor, HasIcon, HasLabel
{
    case AreaRange = 'area_range';
    case PricePerMeter = 'price_per_meter';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::AreaRange => 'Area Range',
            self::PricePerMeter => 'Price Per Meter',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::AreaRange => 'info',
            self::PricePerMeter => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::AreaRange => 'heroicon-o-table-cells',
            self::PricePerMeter => 'heroicon-o-calculator',
        };
    }
}
