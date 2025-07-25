<?php

use App\Http\Controllers\Api\V1\CleanerController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\FavoriteCleanerController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\LogoutController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\CleanerReviewController;
use App\Http\Controllers\Api\V1\DistrictController;
use App\Http\Controllers\Api\V1\UserPreferenceController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RegisterClientController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [LoginController::class, 'store'])
    ->name('api.v1.auth.login');

Route::post('/auth/logout', [LogoutController::class, 'store'])
    ->name('api.v1.auth.logout');

Route::post('/auth/register/client', [RegisterClientController::class, 'store'])
    ->name('api.v1.auth.register.client');

Route::get('/me/preferences', [UserPreferenceController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.profile.preferences.index');
Route::patch('/me/preferences', [UserPreferenceController::class, 'update'])->middleware('auth:sanctum')->name('api.v1.profile.preferences.update');
Route::get('/me', [ProfileController::class, 'index'])->middleware('auth:sanctum')->name('api.v1.profile.index');

Route::get('/cleaners', [CleanerController::class, 'index'])
    ->name('api.v1.cleaner.index');
Route::get('/cleaners/{cleaner}', [CleanerController::class, 'show'])
    ->name('api.v1.cleaner.show');

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

Route::get('/cleaners/{cleaner}/reviews', [CleanerReviewController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.cleaners.reviews.index');
Route::get('/cleaners/{cleaner}/reviews/{review}', [CleanerReviewController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('api.v1.cleaners.reviews.show');
Route::post('/cleaners/{cleaner}/reviews', [CleanerReviewController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.cleaners.reviews.store');

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

Route::get('/districts', [DistrictController::class, 'index'])
    ->name('api.v1.districts.index');
Route::get('/districts/{district}', [DistrictController::class, 'show'])
    ->name('api.v1.districts.show');
