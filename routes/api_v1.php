<?php

use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\FavoriteCleanerController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\PageController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [LoginController::class, 'login'])
    ->name('api.v1.auth.login');

Route::get('/faqs', [FaqController::class, 'index'])
    ->name('api.v1.faq.index');
Route::get('/faqs/{faq}', [FaqController::class, 'show'])
    ->name('api.v1.faq.show');

Route::get('/pages', [PageController::class, 'index'])
    ->name('api.v1.page.index');
Route::get('/pages/{page}', [PageController::class, 'show'])
    ->name('api.v1.page.show');

Route::get('/support-tickets', [SupportTicketController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.support-tickets.index');
Route::get('/support-tickets/{supportTicket}', [SupportTicketController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('api.v1.support-tickets.show');
Route::post('/support-tickets', [SupportTicketController::class, 'store'])
    ->name('api.v1.support-tickets.store');

Route::post('/cleaners/{cleaner}/favorites', [FavoriteCleanerController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.cleaners.favorites.store');
Route::delete('/cleaners/{cleaner}/favorites', [FavoriteCleanerController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.v1.cleaners.favorites.destroy');

Route::get('/notifications', [NotificationController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.index');
Route::get('/notifications/{notification}', [NotificationController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.show');
Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.mark-as-read');
Route::patch('/notifications/read', [NotificationController::class, 'markAllAsRead'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.mark-all-as-read');
Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.destroy.single');
Route::delete('/notifications', [NotificationController::class, 'destroyAll'])
    ->middleware('auth:sanctum')
    ->name('api.v1.notifications.destroy.all');
