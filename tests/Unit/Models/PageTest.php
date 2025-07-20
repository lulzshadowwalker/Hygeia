<?php

namespace Tests\Unit\Models;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_infers_the_slug_automatically_if_not_provided()
    {
        $page = Page::factory()->create([
            'title' => ['en' => 'My First Page'],
            'content' => ['en' => 'This is the content of my first page.'],
        ]);

        $this->assertEquals('my-first-page', $page->slug);
    }

    public function test_it_does_not_override_the_slug_if_provided()
    {
        $page = Page::factory()->create([
            'title' => ['en' => 'My First Page'],
            'content' => ['en' => 'This is the content of my first page.'],
            'slug' => 'custom-slug',
        ]);

        $this->assertEquals('custom-slug', $page->slug);
    }
}
