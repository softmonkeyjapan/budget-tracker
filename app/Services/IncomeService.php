<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Income;
use App\Models\User;
use App\Repositories\Contracts\IncomeRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

final class IncomeService
{
    public function __construct(
        private readonly IncomeRepositoryContract $incomes,
    ) {}

    /**
     * @param  array{amount: int, date: string, description?: string|null}  $data
     */
    public function create(User $user, array $data): Income
    {
        return $this->incomes->create([
            'user_id' => $user->id,
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * @param  array{amount: int, date: string, description?: string|null}  $data
     */
    public function update(Income $income, array $data): Income
    {
        return $this->incomes->update($income, [
            'amount' => $data['amount'],
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function delete(Income $income): void
    {
        $this->incomes->delete($income);
    }

    /**
     * @return Collection<int, Income>
     */
    public function forMonth(User $user, string $month): Collection
    {
        return $this->incomes->forUserAndMonth($user, $month);
    }
}
