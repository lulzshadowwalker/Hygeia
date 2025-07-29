<?php

use App\Http\Controllers\Web\ChatPlaygroundController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Chat Playground Routes
if (app()->environment('local')) {
    Route::middleware('web')->group(function () {
        Route::get('/chat/playground', [ChatPlaygroundController::class, 'index'])->name('chat.playground');
        Route::post('/chat/playground/login-as', [ChatPlaygroundController::class, 'loginAs'])->name('chat.playground.login-as');
    });
}
