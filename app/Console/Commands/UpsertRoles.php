<?php

namespace App\Console\Commands;

use App\Enums\Role;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role as SpatieRole;

class UpsertRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upsert:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upserts roles into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Upserting roles ..');

        foreach (Role::cases() as $role) {
            SpatieRole::findOrCreate($role->value, 'web');
        }

        $this->info('Roles upserted successfully');
    }
}
