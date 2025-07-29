<?php

namespace App\Providers;

use App\Contracts\PushNotificationService;
use App\Contracts\ResponseBuilder;
use App\Http\Response\JsonResponseBuilder;
use App\Models\User;
use App\Services\FirebasePushNotification\FirebasePushNotificationService;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResponseBuilder::class, JsonResponseBuilder::class);
        $this->app->bind(PushNotificationService::class, FirebasePushNotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //  NOTE: I think we can safely remove this line
        Broadcast::routes();

        Gate::define('viewApiDocs', function (User $user) {
            return true;
        });
    }
}
