<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\CategoryDepthExceededException;
use App\Exceptions\CategoryHasChildrenException;
use App\Exceptions\CategoryParentNotFoundException;
use App\Models\Category;
use App\Models\User;
use App\Repositories\Contracts\CategoryRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

final class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryContract $categories,
    ) {}

    /**
     * @param  array{name: string, color?: string|null, icon?: string|null, parent_id?: int|null}  $data
     */
    public function create(User $user, array $data): Category
    {
        $parentId = $data['parent_id'] ?? null;

        if ($parentId !== null) {
            $parent = $this->categories->findOwnedByUser($user, $parentId);

            if ($parent === null) {
                throw new CategoryParentNotFoundException;
            }

            if ($parent->parent_id !== null) {
                throw new CategoryDepthExceededException;
            }
        }

        return $this->categories->create([
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
            'icon' => $data['icon'] ?? null,
        ]);
    }

    /**
     * @param  array{name: string, color?: string|null, icon?: string|null}  $data
     */
    public function update(Category $category, array $data): Category
    {
        return $this->categories->update($category, [
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
            'icon' => $data['icon'] ?? null,
        ]);
    }

    public function delete(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new CategoryHasChildrenException;
        }

        $this->categories->delete($category);
    }

    /**
     * @return Collection<int, Category>
     */
    public function treeForUser(User $user): Collection
    {
        return $this->categories->rootsForUser($user);
    }

    /**
     * Resolve the color a category displays: its own if set, otherwise its parent's.
     */
    public function resolveColor(Category $category): ?string
    {
        return $category->color ?? $category->parent?->color;
    }

    /**
     * Resolve the icon a category displays: its own if set, otherwise its parent's.
     */
    public function resolveIcon(Category $category): ?string
    {
        return $category->icon ?? $category->parent?->icon;
    }
}
