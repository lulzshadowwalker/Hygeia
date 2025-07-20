<?php

namespace App\Observers;

use App\Models\Page;
use Illuminate\Support\Str;

class PageObserver
{
    public function creating(Page $page)
    {
        if (empty($page->slug)) {
            $page->slug = Str::slug($page->getTranslation('title', 'en'));
        }
    }
}
