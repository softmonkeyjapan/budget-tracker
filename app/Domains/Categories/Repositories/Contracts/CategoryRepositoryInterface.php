<?php

declare(strict_types=1);

namespace App\Domains\Categories\Repositories\Contracts;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;

    public function findOwnedByUser(User $user, int $id): ?Category;

    /**
     * @return Collection<int, Category>
     */
    public function rootsForUser(User $user): Collection;
}
