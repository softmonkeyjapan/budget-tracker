<?php

use App\Exceptions\ExpenseCategoryMustBeChildException;
use App\Exceptions\ExpenseCategoryNotFoundException;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\ExpenseRepositoryContract;
use App\Services\ExpenseService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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

test('paginateForMonth passes an allowed per_page through to the repository', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $paginator = Mockery::mock(LengthAwarePaginator::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('paginateForUserAndMonth')
        ->once()
        ->with($user, '2026-08', [], 'date', 'desc', 50, 2)
        ->andReturn($paginator);

    $service = new ExpenseService($expenses, $categories);

    expect($service->paginateForMonth($user, '2026-08', [], 'date', 'desc', 50, 2))->toBe($paginator);
});

test('paginateForMonth clamps a per_page that is not 20, 50 or 100 down to 20', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $paginator = Mockery::mock(LengthAwarePaginator::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('paginateForUserAndMonth')
        ->once()
        ->with($user, '2026-08', [], 'date', 'desc', 20, 1)
        ->andReturn($paginator);

    $service = new ExpenseService($expenses, $categories);

    $service->paginateForMonth($user, '2026-08', [], 'date', 'desc', 13, 1);
});

test('subcategory totals are grouped by leaf category, summed, and sorted by amount descending', function () {
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

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', [])->andReturn(
        new Collection([$expenseOne, $expenseTwo, $expenseThree]),
    );

    $service = new ExpenseService($expenses, $categories);

    $result = $service->subcategoryTotalsForMonth($user, '2026-08');

    expect($result)->toBe([
        ['id' => 11, 'name' => 'Supermarché', 'root_name' => 'Alimentaire', 'color' => '#FF0000', 'amount' => 8000, 'percentage' => 53.3],
        ['id' => 21, 'name' => 'Essence', 'root_name' => 'Transport', 'color' => '#00FF00', 'amount' => 7000, 'percentage' => 46.7],
    ]);
});

test('subcategory totals are filtered when filters are passed through', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null, 'name' => 'Alimentaire', 'color' => '#FF0000']);
    $food = Category::factory()->make(['id' => 11, 'user_id' => 1, 'parent_id' => 10, 'name' => 'Supermarché']);
    $food->setRelation('parent', $root);

    $expense = Expense::factory()->make(['user_id' => 1, 'category_id' => 11, 'amount' => 3000]);
    $expense->setRelation('category', $food);

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', ['category_id' => 11])->andReturn(
        new Collection([$expense]),
    );

    $service = new ExpenseService($expenses, $categories);

    $result = $service->subcategoryTotalsForMonth($user, '2026-08', ['category_id' => 11]);

    expect($result)->toBe([
        ['id' => 11, 'name' => 'Supermarché', 'root_name' => 'Alimentaire', 'color' => '#FF0000', 'amount' => 3000, 'percentage' => 100.0],
    ]);
});

test('subcategory totals are empty when there are no expenses', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', [])->andReturn(new Collection([]));

    $service = new ExpenseService($expenses, $categories);

    expect($service->subcategoryTotalsForMonth($user, '2026-08'))->toBe([]);
});

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

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', [])->andReturn(
        new Collection([$expenseOne, $expenseTwo, $expenseThree]),
    );

    $service = new ExpenseService($expenses, $categories);

    $result = $service->categoryTotalsForMonth($user, '2026-08');

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

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', ['category_id' => 11])->andReturn(
        new Collection([$expense]),
    );

    $service = new ExpenseService($expenses, $categories);

    $result = $service->categoryTotalsForMonth($user, '2026-08', ['category_id' => 11]);

    expect($result)->toBe([
        ['id' => 10, 'name' => 'Alimentaire', 'color' => '#FF0000', 'amount' => 3000, 'percentage' => 100.0],
    ]);
});

test('category totals are empty when there are no expenses', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryContract::class);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndMonth')->once()->with($user, '2026-08', [])->andReturn(new Collection([]));

    $service = new ExpenseService($expenses, $categories);

    expect($service->categoryTotalsForMonth($user, '2026-08'))->toBe([]);
});
