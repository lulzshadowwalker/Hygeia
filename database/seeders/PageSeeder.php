<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $failed = Artisan::call('upsert:pages');
        if ($failed) {
            $this->command->error('Failed to seed pages data');
            return;
        }
    }
}
