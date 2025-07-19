<?php

namespace App\Enums;

//  TODO: Add Filament's HasColor, HasIcon, and HasLabel interfaces to this enum

enum UserStatus: string
{
    case Active = 'active';
    case Banned = 'banned';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Banned => 'danger',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Banned => 'Banned',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Banned => 'heroicon-o-no-symbol',
        };
    }
}
