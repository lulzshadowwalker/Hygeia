<?php

namespace Tests\Unit\Casts;

use App\Models\Booking;
use App\Models\Extra;
use Brick\Money\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoneyCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_money_instance_on_get(): void
    {
        $extra = Extra::factory()->create([
            'amount' => '123.45',
            'currency' => 'HUF',
        ]);

        $this->assertInstanceOf(Money::class, $extra->amount);
        $this->assertSame('123.45', $extra->amount->getAmount()->__toString());
        $this->assertSame('HUF', $extra->amount->getCurrency()->getCurrencyCode());
    }

    public function test_it_sets_money_from_numeric_value_and_defaults_currency(): void
    {
        $extra = Extra::factory()->create([
            'amount' => 123.456,
        ]);

        $extra->refresh();

        $this->assertEquals('123.46', (string) $extra->getRawOriginal('amount'));
        $this->assertSame('HUF', $extra->currency);
    }

    public function test_it_sets_money_from_money_object(): void
    {
        $extra = Extra::factory()->create([
            'amount' => Money::of('999.99', 'HUF'),
        ]);

        $extra->refresh();

        $this->assertEquals('999.99', (string) $extra->getRawOriginal('amount'));
        $this->assertSame('HUF', $extra->currency);
    }

    public function test_it_allows_null_values(): void
    {
        $booking = Booking::factory()->create();
        $booking->selected_amount = null;
        $booking->save();

        $booking->refresh();

        $this->assertNull($booking->selected_amount);
        $this->assertSame('HUF', $booking->currency);
    }
}
