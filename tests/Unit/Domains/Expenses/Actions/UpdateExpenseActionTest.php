<?php

use App\Domains\Expenses\Actions\UpdateExpenseAction;
use App\Domains\Expenses\DataTransferObjects\UpdateExpenseData;
use App\Domains\Expenses\Exceptions\ExpenseCategoryMustBeChildException;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;

test('updating an expense re-validates the category is a child', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => null]);
    $expense = Expense::factory()->make(['id' => 1, 'user_id' => 1, 'category_id' => 5]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn($root);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('update')->never();

    $action = new UpdateExpenseAction($expenses, $categories);

    expect(fn () => $action->execute($user, $expense, new UpdateExpenseData(categoryId: 5, amount: 1000, date: '2026-08-05', description: null)))
        ->toThrow(ExpenseCategoryMustBeChildException::class);
});

it('updates an expense under a child category', function () {
    $user = User::factory()->make(['id' => 1]);
    $child = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => 1]);
    $expense = Expense::factory()->make(['id' => 1, 'user_id' => 1, 'category_id' => 5]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->with($user, 5)->andReturn($child);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('update')
        ->once()
        ->with($expense, [
            'category_id' => 5,
            'amount' => 5000,
            'date' => '2026-08-10',
            'description' => 'Mise à jour',
        ])
        ->andReturn($expense);

    $action = new UpdateExpenseAction($expenses, $categories);

    $result = $action->execute($user, $expense, new UpdateExpenseData(categoryId: 5, amount: 5000, date: '2026-08-10', description: 'Mise à jour'));

    expect($result)->toBe($expense);
});
