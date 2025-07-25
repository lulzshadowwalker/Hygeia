<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Language: string implements HasLabel, HasColor
{
    case Hu = 'hu';
    case En = 'en';

    public static function values(): array
    {
        return array_map(fn(self $language) => $language->value, self::cases());
    }

    public static function default(): string
    {
        return self::tryFrom(config('app.locale'))->value;
    }


    public function label(): string
    {
        return match ($this) {
            self::Hu => 'Hungarian',
            self::En => 'English',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Hu => 'primary',
            self::En => 'info',
        };
    }
}
