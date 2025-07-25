<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Audience: string implements HasLabel, HasColor, HasIcon
{
    case All = 'all';
    case Clients = 'clients';
    case Cleaners = 'cleaners';

    public function label(): string
    {
        return match ($this) {
            self::All => 'All',
            self::Clients => 'Clients',
            self::Cleaners => 'Cleaners',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::All => 'primary',
            self::Clients => 'info',
            self::Cleaners => 'secondary',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::All => 'heroicon-o-globe-alt',
            self::Clients => 'heroicon-o-users',
            self::Cleaners => 'heroicon-o-sparkles',
        };
    }
}
