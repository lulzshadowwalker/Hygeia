<?php

use App\Http\Controllers\Api\V1\FaqController;
use Illuminate\Support\Facades\Route;

Route::get('/faqs', [FaqController::class, 'index'])->name('api.v1.faq.index');
Route::get('/faqs/{faq}', [FaqController::class, 'show'])->name('api.v1.faq.show');

