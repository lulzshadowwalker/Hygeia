<?php

use App\Http\Controllers\Api\V1\AcceptOfferController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CallbackRequestController;
use App\Http\Controllers\Api\V1\ChangePasswordController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\ChatMessageController;
use App\Http\Controllers\Api\V1\ChatRoomController;
use App\Http\Controllers\Api\V1\CleanerController;
use App\Http\Controllers\Api\V1\CleanerDashboardController;
use App\Http\Controllers\Api\V1\CleanerReviewController;
use App\Http\Controllers\Api\V1\CompleteBookingController;
use App\Http\Controllers\Api\V1\DistrictController;
use App\Http\Controllers\Api\V1\ExtraController;
use App\Http\Controllers\Api\V1\FaqController;
use App\Http\Controllers\Api\V1\FavoriteCleanerController;
use App\Http\Controllers\Api\V1\ForgotPasswordController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\LogoutController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OfferController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\ProfileReviewController;
use App\Http\Controllers\Api\V1\RegisterCleanerController;
use App\Http\Controllers\Api\V1\RegisterClientController;
use App\Http\Controllers\Api\V1\ResetPasswordController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\UsernameController;
use App\Http\Controllers\Api\V1\UserPreferenceController;
use App\Http\Middleware\CleanerMiddleware;
use App\Http\Middleware\ClientMiddleware;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [LoginController::class, 'store'])
    ->name('api.v1.auth.login');

Route::post('/auth/logout', [LogoutController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.auth.logout');

Route::post('/auth/forgot-password', [ForgotPasswordController::class, 'store'])
    ->name('api.v1.auth.forgot-password');
Route::post('/auth/reset-password', [ResetPasswordController::class, 'store'])
    ->name('api.v1.auth.reset-password');
Route::post('/auth/change-password', [ChangePasswordController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.auth.change-password');

Route::post('/auth/register/client', [RegisterClientController::class, 'store'])
    ->name('api.v1.auth.register.client');
Route::post('/auth/register/cleaner', [RegisterCleanerController::class, 'store'])
    ->name('api.v1.auth.register.cleaner');

Route::get('/auth/usernames/{username}', [UsernameController::class, 'show'])
    ->name('api.v1.auth.usernames.show');

Route::get('/me/preferences', [UserPreferenceController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.profile.preferences.index');
Route::patch('/me/preferences', [UserPreferenceController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('api.v1.profile.preferences.update');
Route::get('/me', [ProfileController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.profile.index');
Route::patch('/me', [ProfileController::class, 'update'])
    ->middleware('auth:sanctum')
    ->name('api.v1.profile.update');
Route::get('/me/reviews', [ProfileReviewController::class, 'index'])
    ->middleware('auth:sanctum')
    ->middleware(CleanerMiddleware::class)
    ->name('api.v1.profile.reviews.index');
Route::get('/me/dashboard', [CleanerDashboardController::class, 'index'])
    ->middleware('auth:sanctum')
    ->middleware(CleanerMiddleware::class)
    ->name('api.v1.profile.dashboard');

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

Route::post('/callback-requests', [CallbackRequestController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.callback-requests.store');

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

Route::get('/services', [ServiceController::class, 'index'])
    ->name('api.v1.services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])
    ->name('api.v1.services.show');

Route::get('/extras', [ExtraController::class, 'index'])
    ->name('api.v1.extras.index');
Route::get('/extras/{extra}', [ExtraController::class, 'show'])
    ->name('api.v1.extras.show');

Route::post('/bookings', [BookingController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.bookings.store');
Route::get('/bookings', [BookingController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.bookings.index');
Route::get('/bookings/{booking}', [BookingController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('api.v1.bookings.show');
Route::post('/bookings/{booking}/complete', [CompleteBookingController::class, 'store'])
    ->middleware('auth:sanctum')
    ->middleware(ClientMiddleware::class)
    ->name('api.v1.bookings.complete');

Route::post('/offers/{offer}/accept', [AcceptOfferController::class, 'store'])
    ->middleware('auth:sanctum')
    ->middleware(CleanerMiddleware::class)
    ->name('api.v1.offers.accept');

Route::get('/offers', [OfferController::class, 'index'])
    ->middleware('auth:sanctum')
    ->middleware(CleanerMiddleware::class)
    ->name('api.v1.offers.index');
Route::get('/offers/{offer}', [OfferController::class, 'show'])
    ->middleware('auth:sanctum')
    ->middleware(CleanerMiddleware::class)
    ->name('api.v1.offers.show');

Route::get('/invoices', [InvoiceController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.invoices.index');
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('api.v1.invoices.show');

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

Route::get('/chat/rooms', [ChatRoomController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.index');
Route::post('/chat/rooms', action: [ChatRoomController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.store');
Route::get('/chat/rooms/support', [ChatRoomController::class, 'support'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.support');
Route::get('/chat/rooms/{chatRoom}', action: [ChatRoomController::class, 'show'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.show');
Route::post('/chat/rooms/{chatRoom}/join', [ChatRoomController::class, 'join'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.join');
Route::delete('/chat/rooms/{chatRoom}/leave', [ChatRoomController::class, 'leave'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.leave');
Route::get('/chat/rooms/{chatRoom}/messages', [ChatMessageController::class, 'index'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.messages.index');
Route::post('/chat/rooms/{chatRoom}/messages', [ChatMessageController::class, 'store'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.rooms.messages.store');
Route::get('/chat/reverb-config', [ChatController::class, 'getReverbConfig'])
    ->middleware('auth:sanctum')
    ->name('api.v1.chat.reverb-config');
