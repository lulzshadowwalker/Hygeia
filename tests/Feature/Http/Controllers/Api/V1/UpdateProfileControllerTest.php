<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Models\Cleaner;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class UpdateProfileControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_client_can_update_profile()
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $avatar = File::image('avatar.jpg', 200, 200);

        $response = $this->actingAs($client->user)
            ->patchJson(route('api.v1.profile.update'), [
                'data' => [
                    'attributes' => [
                        'name' => 'Updated Name',
                        'phone' => '+962792002803',
                        'avatar' => $avatar,
                    ],
                ],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.attributes.name', 'Updated Name')
            ->assertJsonPath('data.attributes.phone', '+962792002803');

        $client->user->refresh();
        $this->assertEquals('Updated Name', $client->user->name);
        $this->assertEquals('+962792002803', $client->user->phone);
        $this->assertNotNull($client->user->avatar);
    }

    public function test_client_can_partially_update_profile()
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);
        $originalName = $client->user->name;
        $originalPhone = $client->user->phone;

        $response = $this->actingAs($client->user)
            ->patchJson(route('api.v1.profile.update'), [
                'data' => [
                    'attributes' => [
                        'name' => 'New Name Only',
                    ],
                ],
            ]);

        $response->assertOk();

        $client->user->refresh();
        $this->assertEquals('New Name Only', $client->user->name);
        $this->assertEquals($originalPhone, $client->user->phone);
    }

    public function test_cleaner_can_update_profile()
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);
        $avatar = File::image('avatar.jpg', 200, 200);

        $response = $this->actingAs($cleaner->user)
            ->patchJson(route('api.v1.profile.update'), [
                'data' => [
                    'attributes' => [
                        'name' => 'Updated Cleaner Name',
                        'phone' => '+962792002804',
                        'avatar' => $avatar,
                        'yearsOfExperience' => 10,
                        'maxHoursPerWeek' => 35,
                        'serviceRadius' => 25,
                        'availableDays' => ['monday', 'tuesday', 'wednesday'],
                        'timeSlots' => ['morning', 'afternoon'],
                        'hasCleaningSupplies' => false,
                        'comfortableWithPets' => true,
                    ],
                ],
            ]);

        $response->assertOk();

        $cleaner->user->refresh();
        $cleaner->refresh();

        $this->assertEquals('Updated Cleaner Name', $cleaner->user->name);
        $this->assertEquals('+962792002804', $cleaner->user->phone);
        $this->assertEquals(10, $cleaner->years_of_experience);
        $this->assertEquals(35, $cleaner->max_hours_per_week);
        $this->assertEquals(25, $cleaner->service_radius);
        $this->assertEquals(['monday', 'tuesday', 'wednesday'], $cleaner->available_days);
        $this->assertEquals(['morning', 'afternoon'], $cleaner->time_slots);
        $this->assertFalse($cleaner->has_cleaning_supplies);
        $this->assertTrue($cleaner->comfortable_with_pets);
        $this->assertNotNull($cleaner->user->avatar);
    }

    public function test_cleaner_can_partially_update_profile()
    {
        $cleaner = Cleaner::factory()->create([
            'years_of_experience' => 5,
            'max_hours_per_week' => 40,
        ]);
        $cleaner->user->assignRole(Role::Cleaner);

        $response = $this->actingAs($cleaner->user)
            ->patchJson(route('api.v1.profile.update'), [
                'data' => [
                    'attributes' => [
                        'yearsOfExperience' => 8,
                        'hasCleaningSupplies' => true,
                    ],
                ],
            ]);

        $response->assertOk();

        $cleaner->refresh();
        $this->assertEquals(8, $cleaner->years_of_experience);
        $this->assertTrue($cleaner->has_cleaning_supplies);
        $this->assertEquals(40, $cleaner->max_hours_per_week); // Unchanged
    }

    public function test_validation_errors_for_client_update()
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $response = $this->actingAs($client->user)
            ->patchJson(route('api.v1.profile.update'), [
                'data' => [
                    'attributes' => [
                        'name' => str_repeat('a', 256), // Too long
                        'phone' => 'invalid-phone',
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.name',
                'data.attributes.phone',
            ]);
    }

    public function test_validation_errors_for_cleaner_update()
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $response = $this->actingAs($cleaner->user)
            ->patchJson(route('api.v1.profile.update'), [
                'data' => [
                    'attributes' => [
                        'yearsOfExperience' => -1, // Invalid
                        'maxHoursPerWeek' => 101, // Too high
                        'serviceRadius' => 0, // Too low
                        'availableDays' => [], // Empty array
                        'timeSlots' => ['invalid-slot'], // Invalid value
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.yearsOfExperience',
                'data.attributes.maxHoursPerWeek',
                'data.attributes.serviceRadius',
                'data.attributes.availableDays',
                'data.attributes.timeSlots.0',
            ]);
    }

    public function test_requires_authentication()
    {
        $response = $this->patchJson(route('api.v1.profile.update'), [
            'data' => [
                'attributes' => [
                    'name' => 'New Name',
                ],
            ],
        ]);

        $response->assertStatus(401);
    }
}
