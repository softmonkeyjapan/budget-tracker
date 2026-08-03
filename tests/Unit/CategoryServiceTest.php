<?php

use App\Exceptions\CategoryDepthExceededException;
use App\Exceptions\CategoryHasChildrenException;
use App\Exceptions\CategoryHasExpensesException;
use App\Exceptions\CategoryParentNotFoundException;
use App\Models\Category;
use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\ExpenseRepositoryContract;
use App\Services\CategoryService;

test('creating a root category does not check for a parent', function () {
    $user = User::factory()->make(['id' => 1]);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $repository->shouldReceive('findOwnedByUser')->never();
    $repository->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 1,
            'parent_id' => null,
            'name' => 'Alimentaire',
            'color' => null,
            'icon' => null,
        ])
        ->andReturn(new Category(['name' => 'Alimentaire']));

    $service = new CategoryService($repository, Mockery::mock(ExpenseRepositoryContract::class));

    $service->create($user, ['name' => 'Alimentaire']);
});

test('creating a child category under a root is allowed', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null]);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $repository->shouldReceive('findOwnedByUser')->once()->with($user, 10)->andReturn($root);
    $repository->shouldReceive('create')->once()->andReturn(new Category(['name' => 'Boucherie']));

    $service = new CategoryService($repository, Mockery::mock(ExpenseRepositoryContract::class));

    expect(fn () => $service->create($user, ['name' => 'Boucherie', 'parent_id' => 10]))
        ->not->toThrow(Exception::class);
});

test('creating a category under an already-child category throws', function () {
    $user = User::factory()->make(['id' => 1]);
    $child = Category::factory()->make(['id' => 20, 'user_id' => 1, 'parent_id' => 10]);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $repository->shouldReceive('findOwnedByUser')->once()->andReturn($child);
    $repository->shouldReceive('create')->never();

    $service = new CategoryService($repository, Mockery::mock(ExpenseRepositoryContract::class));

    expect(fn () => $service->create($user, ['name' => 'Trop profond', 'parent_id' => 20]))
        ->toThrow(CategoryDepthExceededException::class);
});

test('creating a category under a parent that cannot be found throws', function () {
    $user = User::factory()->make(['id' => 1]);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $repository->shouldReceive('findOwnedByUser')->once()->with($user, 99)->andReturn(null);
    $repository->shouldReceive('create')->never();

    $service = new CategoryService($repository, Mockery::mock(ExpenseRepositoryContract::class));

    expect(fn () => $service->create($user, ['name' => 'Orphelin', 'parent_id' => 99]))
        ->toThrow(CategoryParentNotFoundException::class);
});

test('deleting a category without children or expenses succeeds', function () {
    $category = Mockery::mock(Category::class)->makePartial();
    $category->shouldReceive('children->exists')->andReturn(false);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $repository->shouldReceive('delete')->once()->with($category);

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('existsForCategory')->once()->with($category)->andReturn(false);

    $service = new CategoryService($repository, $expenses);

    expect(fn () => $service->delete($category))->not->toThrow(Exception::class);
});

test('deleting a category with children throws', function () {
    $category = Mockery::mock(Category::class)->makePartial();
    $category->shouldReceive('children->exists')->andReturn(true);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $repository->shouldReceive('delete')->never();

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('existsForCategory')->never();

    $service = new CategoryService($repository, $expenses);

    expect(fn () => $service->delete($category))->toThrow(CategoryHasChildrenException::class);
});

test('deleting a category with expenses throws', function () {
    $category = Mockery::mock(Category::class)->makePartial();
    $category->shouldReceive('children->exists')->andReturn(false);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $repository->shouldReceive('delete')->never();

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('existsForCategory')->once()->with($category)->andReturn(true);

    $service = new CategoryService($repository, $expenses);

    expect(fn () => $service->delete($category))->toThrow(CategoryHasExpensesException::class);
});

test('resolveColor falls back to the parent color when the category has none', function () {
    $root = Category::factory()->make(['user_id' => 1, 'color' => '#2F80ED']);
    $child = Category::factory()->make(['user_id' => 1, 'color' => null]);
    $child->setRelation('parent', $root);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $service = new CategoryService($repository, Mockery::mock(ExpenseRepositoryContract::class));

    expect($service->resolveColor($child))->toBe('#2F80ED');
});

test('resolveColor keeps its own color when set', function () {
    $root = Category::factory()->make(['user_id' => 1, 'color' => '#2F80ED']);
    $child = Category::factory()->make(['user_id' => 1, 'color' => '#FF5B62']);
    $child->setRelation('parent', $root);

    $repository = Mockery::mock(CategoryRepositoryContract::class);
    $service = new CategoryService($repository, Mockery::mock(ExpenseRepositoryContract::class));

    expect($service->resolveColor($child))->toBe('#FF5B62');
});
