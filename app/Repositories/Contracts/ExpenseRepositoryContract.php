<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ExpenseRepositoryContract
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
     * @return Collection<int, Expense>
     */
    public function forUserAndMonth(User $user, string $month): Collection;

    /**
     * @return Collection<int, Expense>
     */
    public function latestForUser(User $user, int $limit): Collection;

    public function existsForCategory(Category $category): bool;
}
