<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitializeApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initializes the application by for example upserting data into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initializing the application ..');

        $this->call(UpsertRoles::class);

        $this->info('Application initialized successfully');
    }
}
