<?php

namespace App\Enums;

enum ChatRoomType: string
{
    case Support = 'support';
    case Standard = 'standard';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
