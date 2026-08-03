<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface IncomeRepositoryContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Income;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Income $income, array $data): Income;

    public function delete(Income $income): void;

    /**
     * @return Collection<int, Income>
     */
    public function forUserAndMonth(User $user, string $month): Collection;
}
