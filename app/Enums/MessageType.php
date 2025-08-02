<?php

namespace App\Enums;

enum MessageType: string
{
    case Text = 'text';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
