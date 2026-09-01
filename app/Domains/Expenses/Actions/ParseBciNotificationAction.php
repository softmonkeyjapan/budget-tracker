<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\DataTransferObjects\ParsedBciNotificationData;

/**
 * Parses the "Paiement carte" push notification sent by the BCI banking app,
 * e.g. "Paiement de 17,99 USD sur la CB 5136...5202 chez NETFLIX.COM."
 *
 * Other BCI notification formats (retrait, virement...) are not recognized yet
 * and will be added here once a real example is available.
 */
final class ParseBciNotificationAction
{
    /**
     * @var array<int, string>
     */
    private const SUPPORTED_CURRENCIES = ['XPF', 'EUR', 'USD'];

    private const PATTERN = '/Paiement de\s+(\d+(?:[.,]\d+)?)\s+([A-Za-z]{3})\s+sur la CB\s+.+?\s+chez\s+(.+)/u';

    public function execute(string $rawBody): ?ParsedBciNotificationData
    {
        if (preg_match(self::PATTERN, $rawBody, $matches) !== 1) {
            return null;
        }

        $currency = strtoupper($matches[2]);

        if (! in_array($currency, self::SUPPORTED_CURRENCIES, true)) {
            return null;
        }

        $merchant = trim($matches[3], " \t\n\r\0\x0B.");

        if ($merchant === '') {
            return null;
        }

        return new ParsedBciNotificationData(
            amount: (float) str_replace(',', '.', $matches[1]),
            currency: $currency,
            merchant: $merchant,
        );
    }
}
