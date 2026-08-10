<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Repositories;

use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Shared\Contracts\ExpenseExistenceInterface;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class EloquentExpenseRepository implements ExpenseExistenceInterface, ExpenseRepositoryInterface
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
     * @param  array{category_id?: int|null, search?: string|null, date?: string|null}  $filters
     * @return Collection<int, Expense>
     */
    public function forUserAndMonth(
        User $user,
        string $month,
        array $filters = [],
        string $sortBy = 'date',
        string $sortDirection = 'desc',
    ): Collection {
        return $this->queryForUserAndMonth($user, $month, $filters, $sortBy, $sortDirection)->get();
    }

    /**
     * @param  array{category_id?: int|null, search?: string|null, date?: string|null}  $filters
     * @return LengthAwarePaginator<int, Expense>
     */
    public function paginateForUserAndMonth(
        User $user,
        string $month,
        array $filters = [],
        string $sortBy = 'date',
        string $sortDirection = 'desc',
        int $perPage = 20,
        int $page = 1,
    ): LengthAwarePaginator {
        return $this->queryForUserAndMonth($user, $month, $filters, $sortBy, $sortDirection)
            ->paginate(perPage: $perPage, page: $page);
    }

    /**
     * @param  array{category_id?: int|null, search?: string|null, date?: string|null}  $filters
     */
    private function queryForUserAndMonth(
        User $user,
        string $month,
        array $filters,
        string $sortBy,
        string $sortDirection,
    ): Builder {
        $query = Expense::query()
            ->where('expenses.user_id', $user->id)
            ->whereYear('expenses.date', substr($month, 0, 4))
            ->whereMonth('expenses.date', substr($month, 5, 2))
            ->with('category.parent');

        if (! empty($filters['category_id'])) {
            $query->where('expenses.category_id', $filters['category_id']);
        }

        if (! empty($filters['search'])) {
            $query->where('expenses.description', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['date'])) {
            $query->whereDate('expenses.date', $filters['date']);
        }

        if ($sortBy === 'category') {
            return $query
                ->join('categories', 'categories.id', '=', 'expenses.category_id')
                ->orderBy('categories.name', $sortDirection)
                ->select('expenses.*');
        }

        $column = in_array($sortBy, ['date', 'description', 'amount'], true) ? $sortBy : 'date';

        $query->orderBy('expenses.'.$column, $sortDirection);

        if ($column === 'date') {
            $query->orderBy('expenses.created_at', $sortDirection);
        }

        return $query;
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
