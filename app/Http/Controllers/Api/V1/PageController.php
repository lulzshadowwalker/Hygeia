<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\PageResource;
use App\Models\Page;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;

#[Group('Content')]
class PageController extends Controller
{
    /**
     * List pages
     *
     * Get a list of all pages.
     *
     * @unauthenticated
     */
    public function index()
    {
        return PageResource::collection(Page::all());
    }

    /**
     * Get a page
     *
     * Get the details of a specific page.
     *
     * @unauthenticated
     */
    public function show(Request $request, Page $page)
    {
        return PageResource::make($page);
    }
}
