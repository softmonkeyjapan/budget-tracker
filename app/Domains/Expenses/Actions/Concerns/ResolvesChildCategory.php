<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions\Concerns;

use App\Domains\Expenses\Exceptions\ExpenseCategoryMustBeChildException;
use App\Domains\Expenses\Exceptions\ExpenseCategoryNotFoundException;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Category;
use App\Models\User;

trait ResolvesChildCategory
{
    private function resolveChildCategory(CategoryLookupInterface $categories, User $user, int $categoryId): Category
    {
        $category = $categories->findOwnedByUser($user, $categoryId);

        if ($category === null) {
            throw new ExpenseCategoryNotFoundException;
        }

        if ($category->parent_id === null) {
            throw new ExpenseCategoryMustBeChildException;
        }

        return $category;
    }
}
