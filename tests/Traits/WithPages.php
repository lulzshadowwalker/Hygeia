<?php

namespace Tests\Traits;

use Database\Seeders\PageSeeder;

trait WithPages
{
    public function setUpWithPages(): void
    {
        $this->seed(PageSeeder::class);
    }
}
