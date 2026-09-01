<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Contracts\ExchangeRateProviderInterface;
use InvalidArgumentException;

final class ConvertAmountToXpfAction
{
    /**
     * Legal peg between the CFP franc and the euro, fixed since 1999 — never fluctuates.
     */
    private const EUR_TO_XPF_RATE = 119.331742;

    public function __construct(
        private readonly ExchangeRateProviderInterface $exchangeRates,
    ) {}

    public function execute(float $amount, string $currency): int
    {
        return match (strtoupper($currency)) {
            'XPF' => (int) round($amount),
            'EUR' => (int) round($amount * self::EUR_TO_XPF_RATE),
            'USD' => (int) round($amount * $this->usdToXpfRate()),
            default => throw new InvalidArgumentException("Devise non supportée : {$currency}"),
        };
    }

    private function usdToXpfRate(): float
    {
        $usdToEur = $this->exchangeRates->usdToEurRate();

        if ($usdToEur !== null) {
            return $usdToEur * self::EUR_TO_XPF_RATE;
        }

        return (float) config('services.exchange_rate.usd_xpf_fallback');
    }
}
