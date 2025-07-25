<?php

namespace App\Enums;

enum Audience: string
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
}
