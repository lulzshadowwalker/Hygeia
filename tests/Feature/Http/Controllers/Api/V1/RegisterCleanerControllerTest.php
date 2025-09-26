<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class RegisterCleanerControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_cleaner_can_register_with_username_and_password()
    {
        $avatar = File::image('avatar.jpg', 200, 200);
        Service::factory()->count(4)->create();

        $response = $this->postJson(route('api.v1.auth.register.cleaner'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'phone' => '+962792002802',
                    'username' => 'username',
                    'email' => 'john@example.com',
                    'password' => 'password',
                    'avatar' => $avatar,
                    'availableDays' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                    'timeSlots' => ['morning', 'afternoon', 'evening'],
                    'maxHoursPerWeek' => 40,
                    'acceptsUrgentOffers' => true,
                    'yearsOfExperience' => 5,
                    'hasCleaningSupplies' => true,
                    'comfortableWithPets' => true,
                    'serviceRadius' => 20,
                    'idCard' => File::image('id_card.jpg', 200, 200),
                    'agreedToTerms' => true,
                ],
                'relationships' => [
                    'previousServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 2],
                        ],
                    ],
                    'preferredServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 3],
                        ],
                    ],
                    'serviceArea' => [
                        'data' => [
                            'type' => 'service',
                            'id' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);

        $user = User::first();
        $this->assertTrue($user->isCleaner);
        $this->assertFalse($user->isClient);
        $this->assertFalse($user->isAdmin);
        $this->assertNotNull($user->cleaner);
        $this->assertEquals(40, $user->cleaner->max_hours_per_week);
        $this->assertCount(2, $user->cleaner->previousServices);
        $this->assertCount(2, $user->cleaner->preferredServices);
        $this->assertNotNull($user->cleaner->idCard);
        $this->assertFileExists($user->cleaner->idCardFile?->getPath() ?? '');
        $this->assertNotNull($user->avatar);
        $this->assertFileExists($user->avatarFile?->getPath() ?? '');
    }

    public function test_cleaner_can_send_device_token_in_registration()
    {
        $deviceToken = 'example-device-token';
        $avatar = File::image('avatar.jpg', 200, 200);
        Service::factory()->count(4)->create();

        $response = $this->postJson(route('api.v1.auth.register.cleaner'), [
            'data' => [
                'attributes' => [
                    'name' => 'John Doe',
                    'phone' => '+962792002802',
                    'username' => 'username',
                    'email' => 'john@example.com',
                    'password' => 'password',
                    'avatar' => $avatar,
                    'availableDays' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                    'timeSlots' => ['morning', 'afternoon', 'evening'],
                    'maxHoursPerWeek' => 40,
                    'acceptsUrgentOffers' => true,
                    'yearsOfExperience' => 5,
                    'hasCleaningSupplies' => true,
                    'comfortableWithPets' => true,
                    'serviceRadius' => 20,
                    'idCard' => File::image('id_card.jpg', 200, 200),
                    'agreedToTerms' => true,
                ],
                'relationships' => [
                    'previousServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 2],
                        ],
                    ],
                    'preferredServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 3],
                        ],
                    ],
                    'serviceArea' => [
                        'data' => [
                            'type' => 'service',
                            'id' => 1,
                        ],
                    ],
                    'deviceTokens' => [
                        'data' => [
                            'type' => 'device-token',
                            'attributes' => [
                                'token' => $deviceToken,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(201);
        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user->deviceTokens()->where('token', $deviceToken)->first());
    }

    public function test_cleaner_cannot_register_with_invalid_data()
    {
        $response = $this->postJson(route('api.v1.auth.register.cleaner'), [
            'data' => [
                'attributes' => [
                    'name' => '',
                    'phone' => 'invalid-phone',
                    'username' => '',
                    'email' => 'invalid-email',
                    'password' => 'short',
                    'availableDays' => 'not-an-array',
                    'timeSlots' => 'not-an-array',
                    'maxHoursPerWeek' => -5,
                    'acceptsUrgentOffers' => 'not-a-boolean',
                    'yearsOfExperience' => -3,
                    'hasCleaningSupplies' => 'not-a-boolean',
                    'comfortableWithPets' => 'not-a-boolean',
                    'serviceRadius' => -10,
                    'agreedToTerms' => false,
                ],
                'relationships' => [
                    'previousServices' => [
                        'data' => [
                            ['type' => 'invalid-type', 'id' => 999],
                        ],
                    ],
                    'preferredServices' => [
                        'data' => [
                            ['type' => 'invalid-type', 'id' => 999],
                        ],
                    ],
                    'deviceTokens' => [
                        'data' => [
                            'type' => 'device-token',
                            'attributes' => [
                                'token' => 12345, // should be string
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'data.attributes.name',
                'data.attributes.phone',
                'data.attributes.username',
                'data.attributes.email',
                'data.attributes.password',
                'data.attributes.availableDays',
                'data.attributes.timeSlots',
                'data.attributes.maxHoursPerWeek',
                'data.attributes.acceptsUrgentOffers',
                'data.attributes.yearsOfExperience',
                'data.attributes.hasCleaningSupplies',
                'data.attributes.comfortableWithPets',
                'data.attributes.serviceRadius',
                'data.attributes.agreedToTerms',
                'data.relationships.previousServices.data.0.type',
                'data.relationships.previousServices.data.0.id',
                'data.relationships.preferredServices.data.0.type',
                'data.relationships.preferredServices.data.0.id',
                'data.relationships.deviceTokens.data.attributes.token',
            ]);
    }

    public function test_cleaner_cannot_register_with_existing_email()
    {
        $user = User::factory()->create();
        $response = $this->postJson(route('api.v1.auth.register.cleaner'), [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'phone' => '+962792002803',
                    'username' => 'jane_doe',
                    'email' => $user->email,
                    'password' => 'password',
                    'availableDays' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                    'timeSlots' => ['morning', 'afternoon', 'evening'],
                    'maxHoursPerWeek' => 40,
                    'acceptsUrgentOffers' => true,
                    'yearsOfExperience' => 5,
                    'hasCleaningSupplies' => true,
                    'comfortableWithPets' => true,
                    'serviceRadius' => 20,
                    'agreedToTerms' => true,
                ],
                'relationships' => [
                    'previousServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 2],
                        ],
                    ],
                    'preferredServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 3],
                        ],
                    ],
                    'serviceArea' => [
                        'data' => [
                            'type' => 'service',
                            'id' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'errors' => [
                    [
                        'status' => '409',
                        'code' => 'Conflict',
                        'title' => 'Email already exists',
                        'detail' => 'An account with this email address already exists',
                        'indicator' => 'EMAIL_ALREADY_EXISTS',
                    ],
                ],
            ]);
    }

    public function test_cleaner_cannot_register_with_existing_username()
    {
        $user = User::factory()->create();
        $response = $this->postJson(route('api.v1.auth.register.cleaner'), [
            'data' => [
                'attributes' => [
                    'name' => 'Jane Doe',
                    'phone' => '+962792002803',
                    'username' => $user->username,
                    'email' => 'john@example.com',
                    'password' => 'password',
                    'availableDays' => ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'],
                    'timeSlots' => ['morning', 'afternoon', 'evening'],
                    'maxHoursPerWeek' => 40,
                    'acceptsUrgentOffers' => true,
                    'yearsOfExperience' => 5,
                    'hasCleaningSupplies' => true,
                    'comfortableWithPets' => true,
                    'serviceRadius' => 20,
                    'agreedToTerms' => true,
                ],
                'relationships' => [
                    'previousServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 2],
                        ],
                    ],
                    'preferredServices' => [
                        'data' => [
                            ['type' => 'service', 'id' => 1],
                            ['type' => 'service', 'id' => 3],
                        ],
                    ],
                    'serviceArea' => [
                        'data' => [
                            'type' => 'service',
                            'id' => 1,
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'errors' => [
                    [
                        'status' => '409',
                        'code' => 'Conflict',
                        'title' => 'Username already exists',
                        'detail' => 'This username is already taken',
                        'indicator' => 'USERNAME_ALREADY_EXISTS',
                    ],
                ],
            ]);
    }
}
