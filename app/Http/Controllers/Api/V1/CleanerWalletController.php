<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\V1\TransactionResource;
use App\Http\Resources\V1\WalletResource;
use App\Models\Cleaner;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Dedoc\Scramble\Attributes\Group;

#[Group('Wallet')]
class CleanerWalletController extends ApiController
{
    public function show(): WalletResource
    {
        $cleaner = auth()->user()->cleaner;
        $wallet = $this->persistWalletIfMissing($cleaner);

        $transactions = Transaction::query()->where('wallet_id', $wallet->id);

        $totalCreditsMinor = (string) (clone $transactions)
            ->where('confirmed', true)
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->sum('amount');

        $totalWithdrawalsMinor = (string) (clone $transactions)
            ->where('confirmed', true)
            ->where('type', Transaction::TYPE_WITHDRAW)
            ->sum('amount');

        return WalletResource::make([
            'id' => $wallet->id,
            'currency' => (string) ($wallet->meta['currency'] ?? 'HUF'),
            'balance' => $cleaner->balanceFloat,
            'transactionCount' => (clone $transactions)->count(),
            'creditsTotal' => $this->formatMinorAmount($totalCreditsMinor, $wallet->decimal_places),
            'withdrawalsTotal' => $this->formatMinorAmount($totalWithdrawalsMinor, $wallet->decimal_places),
            'platformFee' => (string) config('payments.cod.platform_fee', '0.00'),
        ]);
    }

    public function transactions(): \Illuminate\Http\JsonResponse
    {
        $cleaner = auth()->user()->cleaner;
        $wallet = $this->persistWalletIfMissing($cleaner);

        $transactions = Transaction::query()
            ->where('wallet_id', $wallet->id)
            ->with('wallet')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'data' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
            'links' => [
                'first' => $transactions->url(1),
                'last' => $transactions->url($transactions->lastPage()),
                'prev' => $transactions->previousPageUrl(),
                'next' => $transactions->nextPageUrl(),
            ],
        ]);
    }

    private function persistWalletIfMissing(Cleaner $cleaner): Wallet
    {
        $wallet = $cleaner->wallet;

        if (! $wallet->exists) {
            $wallet->save();
        }

        return $wallet->refresh();
    }

    private function formatMinorAmount(string $amountMinor, int $decimalPlaces): string
    {
        $minor = (int) $amountMinor;

        if ($decimalPlaces <= 0) {
            return (string) $minor;
        }

        $sign = $minor < 0 ? '-' : '';
        $absolute = abs($minor);
        $divisor = 10 ** $decimalPlaces;

        return sprintf(
            '%s%d.%0'.(string) $decimalPlaces.'d',
            $sign,
            intdiv($absolute, $divisor),
            $absolute % $divisor
        );
    }
}
