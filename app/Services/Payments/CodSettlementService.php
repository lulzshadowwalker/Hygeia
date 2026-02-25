<?php

namespace App\Services\Payments;

use App\Models\Booking;
use App\Models\Cleaner;
use Bavix\Wallet\Models\Transaction;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class CodSettlementService
{
    public const string PLATFORM_FEE_CONFIG_KEY = 'payments.cod.platform_fee';

    public const string SOURCE = 'cash_on_delivery';

    /**
     * @return array{payout: string, transaction: Transaction}
     */
    public function settle(Booking $booking, Cleaner $cleaner): array
    {
        $payout = $this->calculatePayoutAmount($booking);

        $transaction = $cleaner->depositFloat($payout, [
            'source' => self::SOURCE,
            'booking_id' => $booking->id,
            'booking_currency' => $booking->currency,
            'platform_fee' => $this->platformFee(),
        ]);

        return [
            'payout' => $payout,
            'transaction' => $transaction,
        ];
    }

    public function calculatePayoutAmount(Booking $booking): string
    {
        $amount = Money::of((string) $booking->getRawOriginal('amount'), $booking->currency);
        $platformFee = Money::of($this->platformFee(), $booking->currency);

        if ($platformFee->isGreaterThan($amount)) {
            return '0.00';
        }

        return $amount
            ->minus($platformFee)
            ->getAmount()
            ->toScale(2, RoundingMode::HALF_UP)
            ->__toString();
    }

    public function platformFee(): string
    {
        return (string) config(self::PLATFORM_FEE_CONFIG_KEY, '0.00');
    }
}
