<?php

use App\Domains\Expenses\Queries\GetCategoryTotalsForMonthQuery;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

test('category totals are grouped by root category, summed, and sorted by amount descending', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null, 'name' => 'Alimentaire', 'color' => '#FF0000']);
    $food = Category::factory()->make(['id' => 11, 'user_id' => 1, 'parent_id' => 10, 'name' => 'Supermarché', 'color' => null]);
    $transportRoot = Category::factory()->make(['id' => 20, 'user_id' => 1, 'parent_id' => null, 'name' => 'Transport', 'color' => '#00FF00']);
    $fuel = Category::factory()->make(['id' => 21, 'user_id' => 1, 'parent_id' => 20, 'name' => 'Essence', 'color' => null]);
    $food->setRelation('parent', $root);
    $fuel->setRelation('parent', $transportRoot);

    $expenseOne = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 5000]);
    $expenseOne->setRelation('category', $food);

    $expenseTwo = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 3000]);
    $expenseTwo->setRelation('category', $food);

    $expenseThree = Expense::factory()->make(['user_id' => 1, 'category_id' => 21, 'amount' => 7000]);
    $expenseThree->setRelation('category', $fuel);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', [])->andReturn(
        new Collection([$expenseOne, $expenseTwo, $expenseThree]),
    );

    $query = new GetCategoryTotalsForMonthQuery($expenses);

    $result = $query->execute($user, '2026-08');

    expect($result)->toBe([
        ['id' => 10, 'name' => 'Alimentaire', 'color' => '#FF0000', 'amount' => 8000, 'percentage' => 53.3],
        ['id' => 20, 'name' => 'Transport', 'color' => '#00FF00', 'amount' => 7000, 'percentage' => 46.7],
    ]);
});

test('category totals are filtered when filters are passed through', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null, 'name' => 'Alimentaire', 'color' => '#FF0000']);
    $food = Category::factory()->make(['id' => 11, 'user_id' => 1, 'parent_id' => 10, 'name' => 'Supermarché']);
    $food->setRelation('parent', $root);

    $expense = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 3000]);
    $expense->setRelation('category', $food);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', ['category_id' => 11])->andReturn(
        new Collection([$expense]),
    );

    $query = new GetCategoryTotalsForMonthQuery($expenses);

    $result = $query->execute($user, '2026-08', ['category_id' => 11]);

    expect($result)->toBe([
        ['id' => 10, 'name' => 'Alimentaire', 'color' => '#FF0000', 'amount' => 3000, 'percentage' => 100.0],
    ]);
});

test('category totals are empty when there are no expenses', function () {
    $user = User::factory()->make(['id' => 1]);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', [])->andReturn(new Collection([]));

    $query = new GetCategoryTotalsForMonthQuery($expenses);

    expect($query->execute($user, '2026-08'))->toBe([]);
});
