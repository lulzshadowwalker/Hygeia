<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ServiceType: string implements HasColor, HasIcon, HasLabel
{
    case Residential = 'residential';
    case Commercial = 'commercial';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Residential => 'Residential',
            self::Commercial => 'Commercial',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Residential => 'heroicon-o-home',
            self::Commercial => 'heroicon-o-office-building',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Residential => 'primary',
            self::Commercial => 'info',
        };
    }
}
