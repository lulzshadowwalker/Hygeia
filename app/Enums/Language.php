<?php

namespace App\Enums;

enum Language: string
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
}
