<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Income;
use App\Models\User;
use App\Repositories\Contracts\IncomeRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class IncomeRepository implements IncomeRepositoryContract
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Income
    {
        return Income::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Income $income, array $data): Income
    {
        $income->fill($data);
        $income->save();

        return $income;
    }

    public function delete(Income $income): void
    {
        $income->delete();
    }

    /**
     * @return Collection<int, Income>
     */
    public function forUserAndMonth(User $user, string $month): Collection
    {
        return $this->queryForUserAndMonth($user, $month)->get();
    }

    /**
     * @return LengthAwarePaginator<int, Income>
     */
    public function paginateForUserAndMonth(User $user, string $month, int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        return $this->queryForUserAndMonth($user, $month)->paginate(perPage: $perPage, page: $page);
    }

    private function queryForUserAndMonth(User $user, string $month): Builder
    {
        return Income::query()
            ->where('user_id', $user->id)
            ->whereYear('date', substr($month, 0, 4))
            ->whereMonth('date', substr($month, 5, 2))
            ->orderByDesc('date');
    }

    /**
     * @return Collection<int, Income>
     */
    public function forUserAndDateRange(User $user, string $start, string $end): Collection
    {
        return Income::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start, $end])
            ->get();
    }
}
