<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Policies;

use App\Models\Income;
use App\Models\User;

final class IncomePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }

    public function update(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }

    public function delete(User $user, Income $income): bool
    {
        return $user->id === $income->user_id;
    }
}
