<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Repositories\Contracts;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Expense;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Expense $expense, array $data): Expense;

    public function delete(Expense $expense): void;

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
    ): Collection;

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
    ): LengthAwarePaginator;

    /**
     * @return Collection<int, Expense>
     */
    public function forUserAndDateRange(User $user, string $start, string $end): Collection;

    /**
     * @return Collection<int, Expense>
     */
    public function latestForUser(User $user, int $limit): Collection;

    public function existsForCategory(Category $category): bool;
}
