<?php

use App\Domains\Expenses\Actions\CreateExpenseAction;
use App\Domains\Expenses\DataTransferObjects\CreateExpenseData;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Exceptions\ExpenseCategoryMustBeChildException;
use App\Domains\Expenses\Exceptions\ExpenseCategoryNotFoundException;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

test('creating an expense under a child category succeeds', function () {
    $user = User::factory()->make(['id' => 1]);
    $child = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => 1]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->with($user, 5)->andReturn($child);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 1,
            'category_id' => 5,
            'amount' => 14850,
            'date' => '2026-08-05',
            'description' => 'Supermarché',
            'status' => ExpenseStatus::Validated,
        ])
        ->andReturn(new Expense);

    $action = new CreateExpenseAction($expenses, $categories);

    expect(fn () => $action->execute($user, new CreateExpenseData(
        categoryId: 5,
        amount: 14850,
        date: '2026-08-05',
        description: 'Supermarché',
    )))->not->toThrow(Exception::class);
});

test('creating an expense under a root category throws', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => null]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn($root);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('create')->never();

    $action = new CreateExpenseAction($expenses, $categories);

    expect(fn () => $action->execute($user, new CreateExpenseData(categoryId: 5, amount: 1000, date: '2026-08-05', description: null)))
        ->toThrow(ExpenseCategoryMustBeChildException::class);
});

test('creating an expense under a category that cannot be found throws', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn(null);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('create')->never();

    $action = new CreateExpenseAction($expenses, $categories);

    expect(fn () => $action->execute($user, new CreateExpenseData(categoryId: 99, amount: 1000, date: '2026-08-05', description: null)))
        ->toThrow(ExpenseCategoryNotFoundException::class);
});
