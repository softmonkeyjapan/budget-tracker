<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Queries;

use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class GetDashboardSummaryQuery
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly IncomeRepositoryInterface $incomes,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    /**
     * @return array{
     *     income_total: int,
     *     expense_total: int,
     *     balance: int,
     *     income_count: int,
     *     expense_percentage: float,
     *     categories: array<int, array{id: int, name: string, color: string|null, icon: string|null, amount: int, percentage: float}>,
     *     unspent_percentage: float,
     *     last_expenses: Collection<int, Expense>,
     *     recent_incomes: Collection<int, Income>,
     * }
     */
    public function execute(User $user, string $month): array
    {
        $monthIncomes = $this->incomes->forUserAndMonth($user, $month);
        $incomeTotal = (int) $monthIncomes->sum('amount');

        $monthExpenses = $this->expenses->forUserAndMonth($user, $month);
        $expenseTotal = (int) $monthExpenses->sum('amount');

        $totalsByRoot = [];

        foreach ($monthExpenses as $expense) {
            $rootId = $expense->category->parent_id;
            $totalsByRoot[$rootId] = ($totalsByRoot[$rootId] ?? 0) + $expense->amount;
        }

        $categories = $this->categories->rootsForUser($user)
            ->map(fn ($root) => [
                'id' => $root->id,
                'name' => $root->name,
                'color' => $root->color,
                'icon' => $root->icon,
                'amount' => $totalsByRoot[$root->id] ?? 0,
                'percentage' => $this->percentageOfIncome($totalsByRoot[$root->id] ?? 0, $incomeTotal),
            ])
            ->values()
            ->all();

        $unspentPercentage = $incomeTotal > 0
            ? max(0.0, round((($incomeTotal - $expenseTotal) / $incomeTotal) * 100, 1))
            : 0.0;

        return [
            'income_total' => $incomeTotal,
            'expense_total' => $expenseTotal,
            'balance' => $incomeTotal - $expenseTotal,
            'income_count' => $monthIncomes->count(),
            'expense_percentage' => $this->percentageOfIncome($expenseTotal, $incomeTotal),
            'categories' => $categories,
            'unspent_percentage' => $unspentPercentage,
            'last_expenses' => $monthExpenses->take(5),
            'recent_incomes' => $monthIncomes->sortByDesc('date')->take(5)->values(),
        ];
    }

    private function percentageOfIncome(int $amount, int $incomeTotal): float
    {
        if ($incomeTotal === 0) {
            return 0.0;
        }

        return round(($amount / $incomeTotal) * 100, 1);
    }
}
