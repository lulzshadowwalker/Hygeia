<?php

namespace App\Services\Promocodes;

use App\Enums\BookingStatus;
use App\Enums\PromocodeValidationReason;
use App\Models\Booking;
use App\Models\Promocode;

class PromocodeValidator
{
    public function validate(string $code, bool $lockForUpdate = false): PromocodeValidationResult
    {
        $normalizedCode = strtoupper(trim($code));

        if ($normalizedCode === '') {
            return PromocodeValidationResult::invalid(PromocodeValidationReason::NotFound);
        }

        $promocodeQuery = Promocode::query()->where('code', $normalizedCode);

        if ($lockForUpdate) {
            $promocodeQuery->lockForUpdate();
        }

        $promocode = $promocodeQuery->first();

        if (! $promocode) {
            return PromocodeValidationResult::invalid(PromocodeValidationReason::NotFound);
        }

        if (! $promocode->isActiveAt()) {
            return PromocodeValidationResult::invalid(PromocodeValidationReason::InactivePeriod);
        }

        if ($promocode->max_global_uses !== null) {
            $uses = Booking::query()
                ->where('promocode_id', $promocode->id)
                ->where('status', '!=', BookingStatus::Cancelled->value)
                ->count();

            if ($uses >= $promocode->max_global_uses) {
                return PromocodeValidationResult::invalid(PromocodeValidationReason::UsageLimitReached);
            }
        }

        return PromocodeValidationResult::valid($promocode);
    }
}
