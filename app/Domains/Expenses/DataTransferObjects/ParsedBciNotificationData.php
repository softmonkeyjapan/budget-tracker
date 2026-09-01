<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DataTransferObjects;

final readonly class ParsedBciNotificationData
{
    public function __construct(
        public float $amount,
        public string $currency,
        public string $merchant,
    ) {}
}
