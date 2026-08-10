<?php

declare(strict_types=1);

namespace App\Domains\Categories\Repositories;

use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class EloquentCategoryRepository implements CategoryLookupInterface, CategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->fill($data);
        $category->save();

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function findOwnedByUser(User $user, int $id): ?Category
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->find($id);
    }

    /**
     * @return Collection<int, Category>
     */
    public function rootsForUser(User $user): Collection
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($query) => $query->orderBy('name'),
                'children.parent',
            ])
            ->orderBy('name')
            ->get();
    }
}
