<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([RoleSeeder::class]);

        // User::factory(10)->create();

        User::factory()
            ->has(Client::factory())
            ->create([
                'name' => 'Active Client',
                'username' => 'active_client',
                'email' => 'active@client.com',
                'status' => UserStatus::Active,
            ])
            ->assignRole(Role::Client->value);

        User::factory()
            ->has(Client::factory())
            ->create([
                'name' => 'Banned Client',
                'username' => 'banned_client',
                'email' => 'banned@client.com',
                'status' => UserStatus::Banned,
            ])
            ->assignRole(Role::Client->value);

        User::factory()
            ->has(Cleaner::factory())
            ->create([
                'name' => 'Active Cleaner',
                'username' => 'active_cleaner',
                'email' => 'active@cleaner.com',
                'status' => UserStatus::Active,
            ])
            ->assignRole(Role::Cleaner->value);

        User::factory()
            ->has(Cleaner::factory())
            ->create([
                'name' => 'Banned Cleaner',
                'username' => 'banned_cleaner',
                'email' => 'banned@cleaner.com',
                'status' => UserStatus::Banned,
            ])
            ->assignRole(Role::Cleaner->value);
    }
}
