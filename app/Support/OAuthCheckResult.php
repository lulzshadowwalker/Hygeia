<?php

namespace App\Support;

use App\Enums\Role;

class OAuthCheckResult
{
    public function __construct(
        public bool $exists,
        public string $provider,
        public string $providerId,
        public ?string $email,
        public ?string $name,
        public ?Role $role = null,
        public ?int $userId = null
    ) {
        //
    }
}
