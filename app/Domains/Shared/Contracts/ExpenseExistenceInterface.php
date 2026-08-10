<?php

declare(strict_types=1);

namespace App\Domains\Shared\Contracts;

use App\Models\Category;

interface ExpenseExistenceInterface
{
    public function existsForCategory(Category $category): bool;
}
