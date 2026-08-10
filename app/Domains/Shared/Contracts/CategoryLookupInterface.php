<?php

declare(strict_types=1);

namespace App\Domains\Shared\Contracts;

use App\Models\Category;
use App\Models\User;

interface CategoryLookupInterface
{
    public function findOwnedByUser(User $user, int $id): ?Category;
}
