<?php

namespace App\Enums;

enum PromocodeValidationReason: string
{
    case NotFound = 'not_found';
    case InactivePeriod = 'inactive_period';
    case UsageLimitReached = 'usage_limit_reached';
    case AlreadyUsed = 'already_used';
    case BookingNotEligible = 'booking_not_eligible';
    case Unknown = 'unknown';
}
