<?php

use App\Domains\Expenses\Actions\RejectExpenseAction;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Exceptions\ExpenseAlreadyValidatedException;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Expense;

it('rejects a draft expense', function () {
    $expense = Expense::factory()->make(['id' => 1, 'user_id' => 1, 'category_id' => null, 'status' => ExpenseStatus::Draft]);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('update')->once()->with($expense, ['status' => ExpenseStatus::Rejected])->andReturn($expense);

    $action = new RejectExpenseAction($expenses);

    $action->execute($expense);
});

it('throws when trying to reject an already validated expense', function () {
    $expense = Expense::factory()->make(['id' => 1, 'user_id' => 1, 'category_id' => 1, 'status' => ExpenseStatus::Validated]);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('update')->never();

    $action = new RejectExpenseAction($expenses);

    expect(fn () => $action->execute($expense))->toThrow(ExpenseAlreadyValidatedException::class);
});
