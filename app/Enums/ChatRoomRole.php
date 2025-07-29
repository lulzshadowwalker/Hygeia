<?php

namespace App\Enums;

enum ChatRoomRole: string
{
    case Admin = 'admin';
    case Member = 'member';

    public static function values(): array
    {
        return array_map(fn($role) => $role->value, self::cases());
    }
}
