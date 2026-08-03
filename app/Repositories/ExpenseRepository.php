<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\Contracts\ExpenseRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

final class ExpenseRepository implements ExpenseRepositoryContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Expense $expense, array $data): Expense
    {
        $expense->fill($data);
        $expense->save();

        return $expense;
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    /**
     * @return Collection<int, Expense>
     */
    public function forUserAndMonth(User $user, string $month): Collection
    {
        return Expense::query()
            ->where('user_id', $user->id)
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->with('category.parent')
            ->orderByDesc('date')
            ->get();
    }

    /**
     * @return Collection<int, Expense>
     */
    public function forUserAndDateRange(User $user, string $start, string $end): Collection
    {
        return Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get();
    }

    /**
     * @return Collection<int, Expense>
     */
    public function latestForUser(User $user, int $limit): Collection
    {
        return Expense::query()
            ->where('user_id', $user->id)
            ->with('category.parent')
            ->orderByDesc('date')
            ->limit($limit)
            ->get();
    }

    public function existsForCategory(Category $category): bool
    {
        return Expense::query()->where('category_id', $category->id)->exists();
    }
}
