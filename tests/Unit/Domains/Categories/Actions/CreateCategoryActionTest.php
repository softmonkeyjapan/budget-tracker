<?php

use App\Domains\Categories\Actions\CreateCategoryAction;
use App\Domains\Categories\DataTransferObjects\CreateCategoryData;
use App\Domains\Categories\Exceptions\CategoryDepthExceededException;
use App\Domains\Categories\Exceptions\CategoryParentNotFoundException;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Models\Category;
use App\Models\User;

test('creating a root category does not check for a parent', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('findOwnedByUser')->never();
    $categories->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 1,
            'parent_id' => null,
            'name' => 'Alimentaire',
            'color' => null,
            'icon' => null,
        ])
        ->andReturn(new Category(['name' => 'Alimentaire']));

    $action = new CreateCategoryAction($categories);

    $action->execute($user, new CreateCategoryData(name: 'Alimentaire', color: null, icon: null, parentId: null));
});

test('creating a child category under a root is allowed', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 10, 'user_id' => 1, 'parent_id' => null]);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->with($user, 10)->andReturn($root);
    $categories->shouldReceive('create')->once()->andReturn(new Category(['name' => 'Boucherie']));

    $action = new CreateCategoryAction($categories);

    expect(fn () => $action->execute($user, new CreateCategoryData(name: 'Boucherie', color: null, icon: null, parentId: 10)))
        ->not->toThrow(Exception::class);
});

test('creating a category under an already-child category throws', function () {
    $user = User::factory()->make(['id' => 1]);
    $child = Category::factory()->make(['id' => 20, 'user_id' => 1, 'parent_id' => 10]);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn($child);
    $categories->shouldReceive('create')->never();

    $action = new CreateCategoryAction($categories);

    expect(fn () => $action->execute($user, new CreateCategoryData(name: 'Trop profond', color: null, icon: null, parentId: 20)))
        ->toThrow(CategoryDepthExceededException::class);
});

test('creating a category under a parent that cannot be found throws', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->with($user, 99)->andReturn(null);
    $categories->shouldReceive('create')->never();

    $action = new CreateCategoryAction($categories);

    expect(fn () => $action->execute($user, new CreateCategoryData(name: 'Orphelin', color: null, icon: null, parentId: 99)))
        ->toThrow(CategoryParentNotFoundException::class);
});
