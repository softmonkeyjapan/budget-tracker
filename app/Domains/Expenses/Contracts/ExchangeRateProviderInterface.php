<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Contracts;

interface ExchangeRateProviderInterface
{
    /**
     * Returns null when the rate could not be fetched (network/API failure) so
     * callers can fall back to a configured static rate.
     */
    public function usdToEurRate(): ?float;
}
