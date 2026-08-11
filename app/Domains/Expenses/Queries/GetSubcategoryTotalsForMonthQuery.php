<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Queries;

use App\Domains\Expenses\Queries\Concerns\SortsTotalsByAmountDescending;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\User;

final class GetSubcategoryTotalsForMonthQuery
{
    use SortsTotalsByAmountDescending;

    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    /**
     * @param  array{category_id?: array<int>|null, search?: string|null, date?: string|null}  $filters
     * @return array<int, array{id: int, name: string, root_name: ?string, color: ?string, amount: int, percentage: float}>
     */
    public function execute(User $user, string $month, array $filters = []): array
    {
        $expenses = $this->expenses->forUserAndMonth($user, $month, $filters);

        $totals = [];

        foreach ($expenses as $expense) {
            $category = $expense->category;

            if (! isset($totals[$category->id])) {
                $totals[$category->id] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'root_name' => $category->parent?->name,
                    'color' => $category->resolvedColor(),
                    'amount' => 0,
                ];
            }

            $totals[$category->id]['amount'] += $expense->amount;
        }

        return $this->withPercentagesSortedByAmount($totals);
    }
}
