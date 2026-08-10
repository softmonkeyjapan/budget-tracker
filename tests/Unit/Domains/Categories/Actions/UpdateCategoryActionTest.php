<?php

use App\Domains\Categories\Actions\UpdateCategoryAction;
use App\Domains\Categories\DataTransferObjects\UpdateCategoryData;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Models\Category;

it('updates a category with the given data', function () {
    $category = Category::factory()->make(['user_id' => 1, 'name' => 'Old name']);

    $categories = Mockery::mock(CategoryRepositoryInterface::class);
    $categories->shouldReceive('update')
        ->once()
        ->with($category, [
            'name' => 'New name',
            'color' => '#2F80ED',
            'icon' => null,
        ])
        ->andReturn($category);

    $action = new UpdateCategoryAction($categories);

    $result = $action->execute($category, new UpdateCategoryData(name: 'New name', color: '#2F80ED', icon: null));

    expect($result)->toBe($category);
});
