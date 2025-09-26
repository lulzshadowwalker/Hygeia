<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum BookingUrgency: string implements HasColor, HasIcon, HasLabel
{
    case Flexible = 'flexible';
    case Scheduled = 'scheduled';
    case Urgent = 'urgent'; // same-day

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Flexible => 'Flexible',
            self::Scheduled => 'Scheduled',
            self::Urgent => 'Urgent',
        };
    }

    public function getLabel(): ?string
    {
        return $this->label();
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Flexible => 'heroicon-o-clock',
            self::Scheduled => 'heroicon-o-calendar',
            self::Urgent => 'heroicon-o-exclamation-circle',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Flexible => 'primary',
            self::Scheduled => 'info',
            self::Urgent => 'danger',
        };
    }
}
