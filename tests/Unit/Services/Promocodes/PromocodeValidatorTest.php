<?php

namespace Tests\Unit\Services\Promocodes;

use App\Enums\BookingStatus;
use App\Enums\PromocodeValidationReason;
use App\Models\Booking;
use App\Models\Promocode;
use App\Services\Promocodes\PromocodeValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromocodeValidatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_not_found_for_unknown_code(): void
    {
        $validator = new PromocodeValidator;
        $result = $validator->validate('UNKNOWN');

        $this->assertFalse($result->valid);
        $this->assertSame(PromocodeValidationReason::NotFound, $result->reason);
        $this->assertNull($result->promocode);
    }

    public function test_it_returns_inactive_period_when_out_of_date_range(): void
    {
        Promocode::factory()->create([
            'code' => 'WINTER',
            'starts_at' => now()->addDay(),
            'expires_at' => now()->addDays(2),
        ]);

        $validator = new PromocodeValidator;
        $result = $validator->validate('WINTER');

        $this->assertFalse($result->valid);
        $this->assertSame(PromocodeValidationReason::InactivePeriod, $result->reason);
    }

    public function test_it_returns_usage_limit_reached_when_global_cap_is_hit(): void
    {
        $promocode = Promocode::factory()->create([
            'max_global_uses' => 1,
        ]);

        Booking::factory()->create([
            'promocode_id' => $promocode->id,
            'status' => BookingStatus::Pending->value,
        ]);

        $validator = new PromocodeValidator;
        $result = $validator->validate($promocode->code);

        $this->assertFalse($result->valid);
        $this->assertSame(PromocodeValidationReason::UsageLimitReached, $result->reason);
    }

    public function test_it_returns_valid_for_active_and_available_promocode(): void
    {
        $promocode = Promocode::factory()->create([
            'max_global_uses' => 5,
        ]);

        $validator = new PromocodeValidator;
        $result = $validator->validate($promocode->code);

        $this->assertTrue($result->valid);
        $this->assertNull($result->reason);
        $this->assertSame($promocode->id, $result->promocode?->id);
    }
}
