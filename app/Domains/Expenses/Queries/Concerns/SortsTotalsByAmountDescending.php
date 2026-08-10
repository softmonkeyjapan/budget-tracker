<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Queries\Concerns;

trait SortsTotalsByAmountDescending
{
    /**
     * @param  array<int, array<string, mixed>>  $totals
     * @return array<int, array<string, mixed>>
     */
    private function withPercentagesSortedByAmount(array $totals): array
    {
        $totals = array_values($totals);
        $sum = array_sum(array_column($totals, 'amount'));

        foreach ($totals as &$total) {
            $total['percentage'] = $sum > 0 ? round(($total['amount'] / $sum) * 100, 1) : 0.0;
        }
        unset($total);

        usort($totals, fn (array $a, array $b) => $b['amount'] <=> $a['amount']);

        return $totals;
    }
}
