<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Cleaner;
use App\Models\Client;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class CleanerControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_it_lists_cleaners_with_real_data_and_same_contract(): void
    {
        $district = District::factory()->create();
        $cleaner = Cleaner::factory()->create([
            'service_area_id' => $district->id,
            'available_days' => ['monday', 'wednesday'],
            'time_slots' => ['morning'],
            'max_hours_per_week' => 30,
            'years_of_experience' => 7,
            'has_cleaning_supplies' => true,
            'comfortable_with_pets' => false,
            'service_radius' => 25,
            'agreed_to_terms' => true,
        ]);
        $cleaner->user()->update([
            'name' => 'Cleaner One',
            'phone' => '+36201234567',
            'email' => 'cleaner.one@example.com',
            'status' => UserStatus::Banned->value,
        ]);

        $response = $this->getJson(route('api.v1.cleaner.index'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'cleaner')
            ->assertJsonPath('data.0.id', (string) $cleaner->id)
            ->assertJsonPath('data.0.attributes.name', 'Cleaner One')
            ->assertJsonPath('data.0.attributes.phone', '+36201234567')
            ->assertJsonPath('data.0.attributes.email', 'cleaner.one@example.com')
            ->assertJsonPath('data.0.attributes.status', UserStatus::Banned->value)
            ->assertJsonPath('data.0.attributes.availableDays', ['monday', 'wednesday'])
            ->assertJsonPath('data.0.attributes.maxHoursPerWeek', 30)
            ->assertJsonPath('data.0.attributes.timeSlots', ['morning'])
            ->assertJsonPath('data.0.attributes.yearsOfExperience', 7)
            ->assertJsonPath('data.0.attributes.hasCleaningSupplies', true)
            ->assertJsonPath('data.0.attributes.comfortableWithPets', false)
            ->assertJsonPath('data.0.attributes.serviceRadius', 25)
            ->assertJsonPath('data.0.attributes.agreedToTerms', true)
            ->assertJsonPath('data.0.attributes.isFavorite', false)
            ->assertJsonPath('data.0.includes.serviceArea.id', (string) $district->id)
            ->assertJsonStructure([
                'data' => [[
                    'type',
                    'id',
                    'attributes' => [
                        'name',
                        'phone',
                        'email',
                        'avatar',
                        'status',
                        'availableDays',
                        'maxHoursPerWeek',
                        'timeSlots',
                        'yearsOfExperience',
                        'hasCleaningSupplies',
                        'comfortableWithPets',
                        'serviceRadius',
                        'agreedToTerms',
                        'isFavorite',
                    ],
                    'includes' => [
                        'previousServices',
                        'preferredServices',
                        'serviceArea',
                    ],
                ]],
            ]);
    }

    public function test_it_marks_cleaner_as_favorite_for_authenticated_client(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client->value);
        $cleaner = Cleaner::factory()->create();

        $client->favoriteCleaners()->attach($cleaner->id);

        $this->actingAs($client->user)
            ->getJson(route('api.v1.cleaner.show', $cleaner))
            ->assertOk()
            ->assertJsonPath('data.attributes.isFavorite', true);
    }
}
