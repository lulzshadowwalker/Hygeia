<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Role;
use App\Models\Cleaner;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class CleanerWalletControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_cleaner_can_view_wallet_summary(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $cleaner->depositFloat(50.00, [
            'source' => 'cash_on_delivery',
            'booking_id' => 1,
            'booking_currency' => 'HUF',
        ]);

        $response = $this->actingAs($cleaner->user)
            ->getJson(route('api.v1.profile.wallet.show'));

        $response->assertOk()
            ->assertJsonPath('data.type', 'wallet')
            ->assertJsonPath('data.attributes.balance', '50.00')
            ->assertJsonPath('data.attributes.currency', 'HUF')
            ->assertJsonPath('data.attributes.transactionCount', 1)
            ->assertJsonPath('data.attributes.creditsTotal', '50.00')
            ->assertJsonPath('data.attributes.withdrawalsTotal', '0.00')
            ->assertJsonPath('data.attributes.platformFee', '0.00');
    }

    public function test_cleaner_can_view_wallet_transactions(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $cleaner->depositFloat(12.50, [
            'source' => 'cash_on_delivery',
            'booking_id' => 10,
            'booking_currency' => 'HUF',
        ]);
        $cleaner->depositFloat(13.50, [
            'source' => 'cash_on_delivery',
            'booking_id' => 11,
            'booking_currency' => 'HUF',
        ]);

        $response = $this->actingAs($cleaner->user)
            ->getJson(route('api.v1.profile.wallet.transactions.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.type', 'wallet-transaction')
            ->assertJsonPath('data.0.attributes.source', 'cash_on_delivery')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonStructure([
                'links' => ['first', 'last', 'prev', 'next'],
            ]);
    }

    public function test_client_cannot_access_wallet_endpoints(): void
    {
        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $this->actingAs($client->user)
            ->getJson(route('api.v1.profile.wallet.show'))
            ->assertForbidden();

        $this->actingAs($client->user)
            ->getJson(route('api.v1.profile.wallet.transactions.index'))
            ->assertForbidden();
    }
}
