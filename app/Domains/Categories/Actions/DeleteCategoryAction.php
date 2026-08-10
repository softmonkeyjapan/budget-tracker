<?php

declare(strict_types=1);

namespace App\Domains\Categories\Actions;

use App\Domains\Categories\Exceptions\CategoryHasChildrenException;
use App\Domains\Categories\Exceptions\CategoryHasExpensesException;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Shared\Contracts\ExpenseExistenceInterface;
use App\Models\Category;

final class DeleteCategoryAction
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
        private readonly ExpenseExistenceInterface $expenses,
    ) {}

    public function execute(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new CategoryHasChildrenException;
        }

        if ($this->expenses->existsForCategory($category)) {
            throw new CategoryHasExpensesException;
        }

        $this->categories->delete($category);
    }
}
