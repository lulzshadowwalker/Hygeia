<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Facades\Pulse;

class PulseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::define('viewPulse', fn(User $user) => $user->isAdmin);

        Pulse::user(fn($user) => [
            'name' => $user->fullName,
            'extra' => $user->email,
            'avatar' => $user->avatar,
        ]);
    }
}
