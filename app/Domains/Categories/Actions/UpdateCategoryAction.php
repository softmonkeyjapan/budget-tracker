<?php

declare(strict_types=1);

namespace App\Domains\Categories\Actions;

use App\Domains\Categories\DataTransferObjects\UpdateCategoryData;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Models\Category;

final class UpdateCategoryAction
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function execute(Category $category, UpdateCategoryData $data): Category
    {
        return $this->categories->update($category, [
            'name' => $data->name,
            'color' => $data->color,
            'icon' => $data->icon,
        ]);
    }
}
