<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ExpenseCategoryMustBeChildException;
use App\Exceptions\ExpenseCategoryNotFoundException;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\ExpenseRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

final class ExpenseService
{
    public function __construct(
        private readonly ExpenseRepositoryContract $expenses,
        private readonly CategoryRepositoryContract $categories,
    ) {}

    /**
     * @param  array{category_id: int, amount: int, date: string, description?: string|null}  $data
     */
    public function create(User $user, array $data): Expense
    {
        $category = $this->resolveChildCategory($user, $data['category_id']);

        return $this->expenses->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * @param  array{category_id: int, amount: int, date: string, description?: string|null}  $data
     */
    public function update(User $user, Expense $expense, array $data): Expense
    {
        $category = $this->resolveChildCategory($user, $data['category_id']);

        return $this->expenses->update($expense, [
            'category_id' => $category->id,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function delete(Expense $expense): void
    {
        $this->expenses->delete($expense);
    }

    /**
     * @param  array{category_id?: int|null, search?: string|null, date?: string|null}  $filters
     * @return Collection<int, Expense>
     */
    public function forMonth(
        User $user,
        string $month,
        array $filters = [],
        string $sortBy = 'date',
        string $sortDirection = 'desc',
    ): Collection {
        return $this->expenses->forUserAndMonth($user, $month, $filters, $sortBy, $sortDirection);
    }

    /**
     * @return array<int, array{id: int, name: string, root_name: ?string, color: ?string, amount: int}>
     */
    public function subcategoryTotalsForMonth(User $user, string $month): array
    {
        $expenses = $this->expenses->forUserAndMonth($user, $month);

        $totals = [];

        foreach ($expenses as $expense) {
            $category = $expense->category;

            if (! isset($totals[$category->id])) {
                $totals[$category->id] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'root_name' => $category->parent?->name,
                    'color' => $category->color ?? $category->parent?->color,
                    'amount' => 0,
                ];
            }

            $totals[$category->id]['amount'] += $expense->amount;
        }

        $totals = array_values($totals);

        usort($totals, fn (array $a, array $b) => $b['amount'] <=> $a['amount']);

        return $totals;
    }

    private function resolveChildCategory(User $user, int $categoryId): Category
    {
        $category = $this->categories->findOwnedByUser($user, $categoryId);

        if ($category === null) {
            throw new ExpenseCategoryNotFoundException;
        }

        if ($category->parent_id === null) {
            throw new ExpenseCategoryMustBeChildException;
        }

        return $category;
    }
}
