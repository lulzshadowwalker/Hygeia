<?php

namespace App\Casts;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class MoneyCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Money
    {
        if ($value === null) {
            return null;
        }

        $currency = strtoupper($attributes['currency'] ?? 'HUF');

        return Money::of($value, $currency, null, RoundingMode::HALF_UP);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        if ($value === null) {
            return [$key => null];
        }

        $modelCurrency = strtoupper((string) ($attributes['currency'] ?? $model->getAttribute('currency') ?? 'HUF'));

        if ($value instanceof Money) {
            $valueCurrency = strtoupper($value->getCurrency()->getCurrencyCode());

            if ($modelCurrency !== $valueCurrency) {
                throw new InvalidArgumentException("Currency mismatch for {$key}: {$modelCurrency} !== {$valueCurrency}.");
            }

            $money = $value;
        } elseif (is_numeric($value)) {
            $money = Money::of((string) $value, $modelCurrency, null, RoundingMode::HALF_UP);
        } else {
            throw new InvalidArgumentException("Unsupported money value for {$key}.");
        }

        $amount = $money->getAmount()->toScale(2, RoundingMode::HALF_UP)->__toString();

        return [
            $key => $amount,
            'currency' => $modelCurrency,
        ];
    }
}
