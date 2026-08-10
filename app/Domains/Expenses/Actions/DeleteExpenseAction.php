<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Expense;

final class DeleteExpenseAction
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function execute(Expense $expense): void
    {
        $this->expenses->delete($expense);
    }
}
