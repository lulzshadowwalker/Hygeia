<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\ServiceType;
use App\Enums\UserStatus;
use App\Models\CallbackRequest;
use App\Models\City;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\District;
use App\Models\Extra;
use App\Models\Faq;
use App\Models\Invoice;
use App\Models\Pricing;
use App\Models\Review;
use App\Models\Service;
use App\Models\SupportTicket;
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
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'status' => UserStatus::Active,
            ])
            ->assignRole(Role::Admin->value);

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

        Faq::factory()->count(7)->create();
        City::factory()->count(5)->has(District::factory()->count(3))->create();

        Review::factory()->count(10)->create();
        SupportTicket::factory()->count(10)->create();

        foreach (ServiceType::cases() as $type) {
            Service::factory()
                ->count(3)
                ->state(['type' => $type->value])
                ->create()
                ->each(function ($service) {
                    Pricing::factory()->count(3)->create(['service_id' => $service->id]);
                });
        }

        Extra::factory()->count(5)->create();

        CallbackRequest::factory()->count(10)->create();

        Invoice::factory()->count(10)->create();
    }
}
