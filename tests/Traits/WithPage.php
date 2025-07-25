<?php

namespace Tests\Traits;

use Database\Seeders\PageSeeder;

trait WithPage
{
    public function setUpWithPage(): void
    {
        $this->seed(PageSeeder::class);
    }
}
