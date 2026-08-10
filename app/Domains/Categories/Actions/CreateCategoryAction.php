<?php

declare(strict_types=1);

namespace App\Domains\Categories\Actions;

use App\Domains\Categories\DataTransferObjects\CreateCategoryData;
use App\Domains\Categories\Exceptions\CategoryDepthExceededException;
use App\Domains\Categories\Exceptions\CategoryParentNotFoundException;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Models\Category;
use App\Models\User;

final class CreateCategoryAction
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function execute(User $user, CreateCategoryData $data): Category
    {
        $parentId = $data->parentId;

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
            'name' => $data->name,
            'color' => $data->color,
            'icon' => $data->icon,
        ]);
    }
}
