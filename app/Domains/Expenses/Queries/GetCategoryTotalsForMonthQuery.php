<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Queries;

use App\Domains\Expenses\Queries\Concerns\SortsTotalsByAmountDescending;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\User;

final class GetCategoryTotalsForMonthQuery
{
    use SortsTotalsByAmountDescending;

    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    /**
     * @param  array{category_id?: array<int>|null, search?: string|null, date?: string|null}  $filters
     * @return array<int, array{id: int, name: string, color: ?string, amount: int, percentage: float}>
     */
    public function execute(User $user, string $month, array $filters = []): array
    {
        $expenses = $this->expenses->forUserAndMonth($user, $month, $filters);

        $totals = [];

        foreach ($expenses as $expense) {
            $root = $expense->category->parent ?? $expense->category;

            if (! isset($totals[$root->id])) {
                $totals[$root->id] = [
                    'id' => $root->id,
                    'name' => $root->name,
                    'color' => $root->color,
                    'amount' => 0,
                ];
            }

            $totals[$root->id]['amount'] += $expense->amount;
        }

        return $this->withPercentagesSortedByAmount($totals);
    }
}
