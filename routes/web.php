<?php

use App\Http\Controllers\Admin\SupportChatController;
use App\Http\Controllers\Web\ChatPlaygroundController;
use App\Http\Controllers\Web\PasswordResetController;
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

// Password reset routes for email links
Route::get('/password/reset/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [PasswordResetController::class, 'reset'])->name('password.update');

// Chat Playground Routes
if (app()->environment('local')) {
    Route::middleware('web')->group(function () {
        Route::get('/chat/playground', [ChatPlaygroundController::class, 'index'])->name('chat.playground');
        Route::post('/chat/playground/login-as', [ChatPlaygroundController::class, 'loginAs'])->name('chat.playground.login-as');
    });
}
