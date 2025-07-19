<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FaqResource;
use App\Models\Faq;

class FaqController extends Controller
{
    public function index()
    {
        return FaqResource::collection(Faq::all());
    }

    public function show(Faq $faq)
    {
        return FaqResource::make($faq);
    }
}
