<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FaqResource;
use App\Models\Faq;
use Dedoc\Scramble\Attributes\Group;

#[Group('Content')]
class FaqController extends Controller
{
    /**
     * List FAQs
     *
     * Get a list of all frequently asked questions.
     *
     * @unauthenticated
     */
    public function index()
    {
        return FaqResource::collection(Faq::all());
    }

    /**
     * Get a FAQ
     *
     * Get the details of a specific frequently asked question.
     *
     * @unauthenticated
     */
    public function show(Faq $faq)
    {
        return FaqResource::make($faq);
    }
}
