<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Adapters;

use App\Domains\Expenses\Contracts\ExchangeRateProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

final class FrankfurterExchangeRateProvider implements ExchangeRateProviderInterface
{
    private const ENDPOINT = 'https://api.frankfurter.dev/v1/latest';

    public function usdToEurRate(): ?float
    {
        try {
            $response = Http::timeout(3)->get(self::ENDPOINT, [
                'base' => 'USD',
                'symbols' => 'EUR',
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $rate = $response->json('rates.EUR');

        return is_numeric($rate) ? (float) $rate : null;
    }
}
