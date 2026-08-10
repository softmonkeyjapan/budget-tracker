<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Queries;

use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Carbon;

final class GetComparisonSummaryQuery
{
    private const MIN_MONTHS = 1;

    private const MAX_MONTHS = 24;

    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly IncomeRepositoryInterface $incomes,
    ) {}

    /**
     * @return array{
     *     months: array<int, array{month: string, income: int, expense: int, balance: int}>,
     *     income_total: int,
     *     expense_total: int,
     *     balance_total: int,
     *     average_income: int,
     *     average_expense: int,
     *     best_balance_month: array{month: string, income: int, expense: int, balance: int}|null,
     * }
     */
    public function execute(User $user, int $months): array
    {
        $months = max(self::MIN_MONTHS, min(self::MAX_MONTHS, $months));
        $start = Carbon::now()->startOfMonth()->subMonths($months - 1);
        $end = $start->copy()->addMonths($months - 1)->endOfMonth();

        $incomesByMonth = $this->incomes
            ->forUserAndDateRange($user, $start->toDateString(), $end->toDateString())
            ->groupBy(fn ($income) => $income->date->format('Y-m'));

        $expensesByMonth = $this->expenses
            ->forUserAndDateRange($user, $start->toDateString(), $end->toDateString())
            ->groupBy(fn ($expense) => $expense->date->format('Y-m'));

        $rows = collect(range(0, $months - 1))
            ->map(function (int $offset) use ($start, $incomesByMonth, $expensesByMonth) {
                $month = $start->copy()->addMonths($offset)->format('Y-m');

                $income = (int) $incomesByMonth->get($month, collect())->sum('amount');
                $expense = (int) $expensesByMonth->get($month, collect())->sum('amount');

                return [
                    'month' => $month,
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense,
                ];
            })
            ->values();

        $incomeTotal = (int) $rows->sum('income');
        $expenseTotal = (int) $rows->sum('expense');

        return [
            'months' => $rows->all(),
            'income_total' => $incomeTotal,
            'expense_total' => $expenseTotal,
            'balance_total' => $incomeTotal - $expenseTotal,
            'average_income' => (int) round($rows->avg('income')),
            'average_expense' => (int) round($rows->avg('expense')),
            'best_balance_month' => $rows->sortByDesc('balance')->first(),
        ];
    }
}
