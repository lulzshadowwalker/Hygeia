<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Cleaner;
use App\Models\Client;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithRoles;

class ConfirmCashReceivedControllerTest extends TestCase
{
    use RefreshDatabase, WithRoles;

    public function test_cleaner_can_confirm_cash_received_once(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()->for($cleaner)->create([
            'status' => BookingStatus::Confirmed,
            'amount' => 125.50,
            'currency' => 'HUF',
            'payment_method' => PaymentMethod::Cod,
        ]);

        $this->actingAs($cleaner->user)
            ->postJson(route('api.v1.bookings.cash-received', $booking))
            ->assertOk()
            ->assertJsonPath('data.attributes.isCashReceived', true)
            ->assertJsonPath('data.attributes.cashReceivedAmount', '125.50')
            ->assertJsonPath('data.attributes.paymentMethod', PaymentMethod::Cod->value);

        $booking->refresh();
        $cleaner->refresh();

        $this->assertNotNull($booking->cash_received_at);
        $this->assertSame('125.50', number_format((float) $booking->cash_received_amount, 2, '.', ''));
        $this->assertSame('HUF', $booking->cash_received_currency);
        $this->assertNotNull($booking->cash_received_wallet_transaction_id);
        $this->assertEquals('125.50', $cleaner->balanceFloat);

        $transaction = Transaction::query()->find($booking->cash_received_wallet_transaction_id);
        $this->assertNotNull($transaction);
        $this->assertSame('cash_on_delivery', data_get($transaction->meta, 'source'));
        $this->assertSame($booking->id, data_get($transaction->meta, 'booking_id'));
    }

    public function test_cleaner_cannot_confirm_cash_received_twice(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()->for($cleaner)->create([
            'status' => BookingStatus::Confirmed,
            'amount' => 40.00,
            'currency' => 'HUF',
            'payment_method' => PaymentMethod::Cod,
        ]);

        $this->actingAs($cleaner->user)
            ->postJson(route('api.v1.bookings.cash-received', $booking))
            ->assertOk();

        $this->actingAs($cleaner->user)
            ->postJson(route('api.v1.bookings.cash-received', $booking))
            ->assertStatus(409)
            ->assertJsonPath('errors.0.title', 'Cash Already Confirmed');

        $cleaner->refresh();
        $this->assertEquals('40.00', $cleaner->balanceFloat);
        $this->assertCount(1, $cleaner->walletTransactions()->get());
    }

    public function test_other_cleaner_cannot_confirm_cash_for_booking(): void
    {
        $bookingCleaner = Cleaner::factory()->create();
        $bookingCleaner->user->assignRole(Role::Cleaner);

        $otherCleaner = Cleaner::factory()->create();
        $otherCleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()->for($bookingCleaner)->create([
            'status' => BookingStatus::Confirmed,
            'payment_method' => PaymentMethod::Cod,
        ]);

        $this->actingAs($otherCleaner->user)
            ->postJson(route('api.v1.bookings.cash-received', $booking))
            ->assertForbidden();
    }

    public function test_client_cannot_confirm_cash_received(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $client = Client::factory()->create();
        $client->user->assignRole(Role::Client);

        $booking = Booking::factory()->for($cleaner)->create([
            'status' => BookingStatus::Confirmed,
            'payment_method' => PaymentMethod::Cod,
        ]);

        $this->actingAs($client->user)
            ->postJson(route('api.v1.bookings.cash-received', $booking))
            ->assertForbidden();
    }

    public function test_cleaner_cannot_confirm_cash_for_non_confirmed_booking(): void
    {
        $cleaner = Cleaner::factory()->create();
        $cleaner->user->assignRole(Role::Cleaner);

        $booking = Booking::factory()->for($cleaner)->create([
            'status' => BookingStatus::Pending,
            'payment_method' => PaymentMethod::Cod,
        ]);

        $this->actingAs($cleaner->user)
            ->postJson(route('api.v1.bookings.cash-received', $booking))
            ->assertStatus(400)
            ->assertJsonPath('errors.0.title', 'Invalid Booking Status');
    }
}
