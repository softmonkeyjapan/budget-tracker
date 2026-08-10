<?php

use App\Domains\Categories\Actions\DeleteCategoryAction;
use App\Domains\Categories\Exceptions\CategoryHasChildrenException;
use App\Domains\Categories\Exceptions\CategoryHasExpensesException;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Shared\Contracts\ExpenseExistenceInterface;
use App\Models\Category;

test('deleting a category without children or expenses succeeds', function () {
    $category = Mockery::mock(Category::class)->makePartial();
    $category->shouldReceive('children->exists')->andReturn(false);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('delete')->once()->with($category);

    $expenses = Mockery::mock(ExpenseExistenceInterface::class);
    $expenses->shouldReceive('existsForCategory')->once()->with($category)->andReturn(false);

    $action = new DeleteCategoryAction($categories, $expenses);

    expect(fn () => $action->execute($category))->not->toThrow(Exception::class);
});

test('deleting a category with children throws', function () {
    $category = Mockery::mock(Category::class)->makePartial();
    $category->shouldReceive('children->exists')->andReturn(true);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('delete')->never();

    $expenses = Mockery::mock(ExpenseExistenceInterface::class);
    $expenses->shouldReceive('existsForCategory')->never();

    $action = new DeleteCategoryAction($categories, $expenses);

    expect(fn () => $action->execute($category))->toThrow(CategoryHasChildrenException::class);
});

test('deleting a category with expenses throws', function () {
    $category = Mockery::mock(Category::class)->makePartial();
    $category->shouldReceive('children->exists')->andReturn(false);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('delete')->never();

    $expenses = Mockery::mock(ExpenseExistenceInterface::class);
    $expenses->shouldReceive('existsForCategory')->once()->with($category)->andReturn(true);

    $action = new DeleteCategoryAction($categories, $expenses);

    expect(fn () => $action->execute($category))->toThrow(CategoryHasExpensesException::class);
});
