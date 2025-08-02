<?php

use App\Http\Controllers\Admin\SupportChatController;
use App\Http\Controllers\Web\ChatPlaygroundController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Admin Support Chat Routes
Route::middleware(['auth', 'web'])->prefix('admin/support')->name('admin.support.')->group(function () {
    Route::get('/chat', [SupportChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/{chatRoom}', [SupportChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{chatRoom}/messages', [SupportChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/chat-config/reverb', [SupportChatController::class, 'getReverbConfig'])->name('chat.reverbconfig');
});

// Chat Playground Routes
if (app()->environment('local')) {
    Route::middleware('web')->group(function () {
        Route::get('/chat/playground', [ChatPlaygroundController::class, 'index'])->name('chat.playground');
        Route::post('/chat/playground/login-as', [ChatPlaygroundController::class, 'loginAs'])->name('chat.playground.login-as');
    });
}
