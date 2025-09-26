<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class UpsertPages extends Command
{
    protected $signature = 'upsert:pages';

    protected $description = 'Upserts pages into the database';

    public function handle()
    {
        $pages = [
            [
                'title' => ['en' => 'About Us', 'ar' => 'من نحن'],
                'content' => ['en' => 'About us content', 'ar' => 'محتوى من نحن'],
            ],
            [
                'title' => ['en' => 'Terms and conditions', 'ar' => 'الشروط والأحكام'],
                'content' => ['en' => 'Terms and conditions content', 'ar' => 'محتوى الشروط والأحكام'],
            ],
            [
                'title' => ['en' => 'Privacy policy', 'ar' => 'سياسة الخصوصية'],
                'content' => ['en' => 'Privacy policy content', 'ar' => 'محتوى سياسة الخصوصية'],
            ],
        ];

        foreach ($pages as $page) {
            if (Page::where('title', 'like', '%'.$page['title']['en'].'%')->exists()) {
                $this->info('Page with title "'.$page['title']['en'].'" already exists, skipping');

                continue;
            }

            Page::firstOrCreate(
                ['title' => $page['title']],
                ['content' => $page['content']]
            );
        }

        $this->info('Pages upserted successfully');
    }
}
