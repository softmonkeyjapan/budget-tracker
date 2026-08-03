<?php

use App\Exceptions\ExpenseCategoryMustBeChildException;
use App\Exceptions\ExpenseCategoryNotFoundException;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\ExpenseRepositoryContract;
use App\Services\ExpenseService;

test('creating an expense under a child category succeeds', function () {
    $user = User::factory()->make(['id' => 1]);
    $child = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);
    $categories->shouldReceive('findOwnedByUser')->once()->with($user, 5)->andReturn($child);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 1,
            'category_id' => 5,
            'amount' => 14850,
            'date' => '2026-08-05',
            'description' => 'Supermarché',
        ])
        ->andReturn(new Expense);

    $service = new ExpenseService($expenses, $categories);

    expect(fn () => $service->create($user, [
        'category_id' => 5,
        'amount' => 14850,
        'date' => '2026-08-05',
        'description' => 'Supermarché',
    ]))->not->toThrow(Exception::class);
});

test('creating an expense under a root category throws', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => null]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn($root);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('create')->never();

    $service = new ExpenseService($expenses, $categories);

    expect(fn () => $service->create($user, ['category_id' => 5, 'amount' => 1000, 'date' => '2026-08-05']))
        ->toThrow(ExpenseCategoryMustBeChildException::class);
});

test('creating an expense under a category that cannot be found throws', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn(null);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('create')->never();

    $service = new ExpenseService($expenses, $categories);

    expect(fn () => $service->create($user, ['category_id' => 99, 'amount' => 1000, 'date' => '2026-08-05']))
        ->toThrow(ExpenseCategoryNotFoundException::class);
});

test('updating an expense re-validates the category is a child', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => null]);
    $expense = Expense::factory()->make(['id' => 1, 'user_id' => 1, 'category_id' => 5]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn($root);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('update')->never();

    $service = new ExpenseService($expenses, $categories);

    expect(fn () => $service->update($user, $expense, ['category_id' => 5, 'amount' => 1000, 'date' => '2026-08-05']))
        ->toThrow(ExpenseCategoryMustBeChildException::class);
});
