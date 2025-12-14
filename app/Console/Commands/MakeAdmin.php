<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Name');
        $username = $this->ask('Username', Str::of($name)->slug('_'));
        $email = $this->ask('Email');

        $password = Str::password(16);

        User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ])->assignRole(Role::Admin->value);

        $this->info('Admin user created successfully!');

        $this->newLine();
        $this->info(route('filament.admin.auth.login'));
        $this->info('Email: '.$email);
        $this->info('Password: '.$password);
        $this->newLine();
    }
}
