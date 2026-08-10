<?php

use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Domains\Reporting\Queries\GetDashboardSummaryQuery;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

test('usage percentage is calculated against the month income total', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null, 'name' => 'Alimentaire']);
    $child = Category::factory()->make(['id' => 11, 'user_id' => 1, 'parent_id' => 10]);

    $expense = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 25000]);
    $expense->setRelation('category', $child);

    $incomes = Mockery::mock(IncomeRepositoryInterface::class);
    $incomes->shouldReceive('forUserAndMonth')->once()->andReturn(
        new Collection([Income::factory()->make(['user_id' => 1, 'amount' => 100000])]),
    );

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->andReturn(new Collection([$expense]));

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('rootsForUser')->once()->andReturn(new Collection([$root]));

    $query = new GetDashboardSummaryQuery($expenses, $incomes, $categories);

    $result = $query->execute($user, '2026-08');

    expect($result['income_total'])->toBe(100000);
    expect($result['expense_total'])->toBe(25000);
    expect($result['balance'])->toBe(75000);
    expect($result['income_count'])->toBe(1);
    expect($result['expense_percentage'])->toBe(25.0);
    expect($result['categories'][0]['percentage'])->toBe(25.0);
    expect($result['unspent_percentage'])->toBe(75.0);
    expect($result['recent_incomes'])->toHaveCount(1);
});

test('unspent percentage is clamped to zero when expenses exceed income', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null]);
    $child = Category::factory()->make(['id' => 11, 'user_id' => 1, 'parent_id' => 10]);

    $expense = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 150000]);
    $expense->setRelation('category', $child);

    $incomes = Mockery::mock(IncomeRepositoryInterface::class);
    $incomes->shouldReceive('forUserAndMonth')->once()->andReturn(
        new Collection([Income::factory()->make(['user_id' => 1, 'amount' => 100000])]),
    );

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->andReturn(new Collection([$expense]));

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('rootsForUser')->once()->andReturn(new Collection([$root]));

    $query = new GetDashboardSummaryQuery($expenses, $incomes, $categories);

    $result = $query->execute($user, '2026-08');

    expect($result['unspent_percentage'])->toBe(0.0);
});

test('zero income month does not divide by zero', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null]);
    $child = Category::factory()->make(['id' => 11, 'user_id' => 1, 'parent_id' => 10]);

    $expense = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 5000]);
    $expense->setRelation('category', $child);

    $incomes = Mockery::mock(IncomeRepositoryInterface::class);
    $incomes->shouldReceive('forUserAndMonth')->once()->andReturn(new Collection([]));

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->andReturn(new Collection([$expense]));

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('rootsForUser')->once()->andReturn(new Collection([$root]));

    $query = new GetDashboardSummaryQuery($expenses, $incomes, $categories);

    $result = $query->execute($user, '2026-08');

    expect($result['income_total'])->toBe(0);
    expect($result['categories'][0]['percentage'])->toBe(0.0);
});

test('only the 5 most recent expenses are kept', function () {
    $user = User::factory()->make(['id' => 1]);
    $child = Category::factory()->make(['id' => 11, 'user_id' => 1, 'parent_id' => 10]);

    $expenseModels = collect(range(1, 7))->map(function ($i) use ($child) {
        $expense = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 1000]);
        $expense->setRelation('category', $child);

        return $expense;
    });

    $incomes = Mockery::mock(IncomeRepositoryInterface::class);
    $incomes->shouldReceive('forUserAndMonth')->once()->andReturn(new Collection([]));

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->andReturn(new Collection($expenseModels->all()));

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('rootsForUser')->once()->andReturn(new Collection([]));

    $query = new GetDashboardSummaryQuery($expenses, $incomes, $categories);

    $result = $query->execute($user, '2026-08');

    expect($result['last_expenses'])->toHaveCount(5);
});
