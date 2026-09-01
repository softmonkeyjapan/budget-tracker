<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Exceptions\ExpenseAlreadyValidatedException;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Expense;

final class RejectExpenseAction
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function execute(Expense $expense): Expense
    {
        if ($expense->status === ExpenseStatus::Validated) {
            throw new ExpenseAlreadyValidatedException;
        }

        return $this->expenses->update($expense, ['status' => ExpenseStatus::Rejected]);
    }
}
