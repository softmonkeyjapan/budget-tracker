<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Actions;

use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\Income;

final class DeleteIncomeAction
{
    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
    ) {}

    public function execute(Income $income): void
    {
        $this->incomes->delete($income);
    }
}
