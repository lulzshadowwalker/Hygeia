<?php

namespace Tests\Traits;

use App\Enums\Role;
use App\Models\User;

trait WithAdmin
{
    use WithRoles;

    protected User $admin;

    public function setUpWithAdmin(): void
    {
        $this->setUpWithRoles();

        $user = User::factory()->create();
        $user->assignRole(Role::Admin->value);
        $this->actingAs($user);
        $this->admin = $user;
    }
}
