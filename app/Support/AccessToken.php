<?php

namespace App\Support;

use App\Enums\Role;

class AccessToken
{
    public function __construct(public string $accessToken, public Role $role)
    {
        //
    }
}
