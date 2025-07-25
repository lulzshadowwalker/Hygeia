<?php

namespace App\Support;

use App\Contracts\NotificationStrategy;
use Illuminate\Support\Collection;

class NotificationStrategyCollection extends Collection
{
    /**
     * NotificationStrategy|array<NotificationStrategy
     */
    public static function make($strategies = [])
    {
        if ($strategies instanceof NotificationStrategyCollection) {
            return $strategies;
        }

        if (is_array($strategies)) {
            return new self($strategies);
        }

        return new self([$strategies]);
    }

    public function match($notifiable): ?NotificationStrategy
    {
        foreach ($this as $strategy) {
            if ($strategy->isSatisfiedBy($notifiable)) return $strategy;
        }

        return null;
    }
}
