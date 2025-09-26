<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Language;
use App\Http\Resources\V1\UserPreferenceResource;
use App\Models\User;
use App\Models\UserPreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_user_preferences()
    {
        $user = User::factory()
            ->has(UserPreferences::factory(), 'preferences')
            ->create();
        $resource = UserPreferenceResource::make($user->preferences);
        $request = Request::create(route('api.v1.profile.preferences.index', ['lang' => Language::En]), 'get');
        $this->actingAs($user);

        $this->getJson(route('api.v1.profile.preferences.index', ['lang' => Language::En]))
            ->assertOk()
            ->assertExactJson(
                $resource->response($request)->getData(true),
            );
    }

    public function test_it_updates_preferences()
    {
        $user = User::factory()
            ->has(UserPreferences::factory()->state([
                'language' => Language::En,
                'email_notifications' => true,
                'push_notifications' => true,
            ]), 'preferences')
            ->create();

        $this->actingAs($user);

        $this->assertEquals($user->preferences->language->value, 'en');
        $this->patchJson(route('api.v1.profile.preferences.update', ['lang' => Language::En]), [
            'data' => [
                'attributes' => [
                    'language' => 'hu',
                    'emailNotifications' => false,
                    'pushNotifications' => false,
                ],
            ],
        ])->assertOk();

        $user->refresh();
        $this->assertEquals('hu', $user->preferences->language->value);
        $this->assertFalse($user->preferences->email_notifications);
        $this->assertFalse($user->preferences->push_notifications);
    }
}
