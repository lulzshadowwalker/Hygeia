<?php

namespace App\Enums;

enum Role: string
{
    case Client = 'client';
    case Cleaner = 'cleaner';
    case Admin = 'admin';
}
