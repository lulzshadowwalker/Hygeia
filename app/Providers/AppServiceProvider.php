<?php

namespace App\Providers;

use App\Contracts\PushNotificationService;
use App\Contracts\ResponseBuilder;
use App\Http\Response\JsonResponseBuilder;
use App\Models\User;
use App\Services\FirebasePushNotification\FirebasePushNotificationService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

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
        // Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:sanctum']]);
        Broadcast::routes();

        Gate::define('viewApiDocs', function (User $user) {
            return true;
        });

        Blade::directive('disabled', function ($expression) {
            return "<?php echo ($expression) ? 'disabled' : ''; ?>";
        });

        // Register Socialite providers for OAuth authentication
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
            $event->extendSocialite('facebook', \SocialiteProviders\Facebook\Provider::class);
            $event->extendSocialite('apple', \SocialiteProviders\Apple\Provider::class);
        });
    }
}
