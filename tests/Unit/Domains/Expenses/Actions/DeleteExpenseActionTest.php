<?php

use App\Domains\Expenses\Actions\DeleteExpenseAction;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Expense;

it('deletes the given expense', function () {
    $expense = Expense::factory()->make(['user_id' => 1, 'category_id' => 1]);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('delete')->once()->with($expense);

    $action = new DeleteExpenseAction($expenses);

    $action->execute($expense);
});
