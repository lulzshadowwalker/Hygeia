<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel, HasColor, HasIcon
{
    case Client = 'client';
    case Cleaner = 'cleaner';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::Cleaner => 'Cleaner',
            self::Admin => 'Admin',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Client => 'heroicon-o-user',
            self::Cleaner => 'heroicon-o-sparkles',
            self::Admin => 'heroicon-o-shield-check',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Client => 'primary',
            self::Cleaner => 'info',
            self::Admin => 'secondary',
        };
    }
}
