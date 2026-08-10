<?php

declare(strict_types=1);

namespace App\Domains\Categories\Http\Controllers;

use App\Domains\Categories\Actions\CreateCategoryAction;
use App\Domains\Categories\Actions\DeleteCategoryAction;
use App\Domains\Categories\Actions\UpdateCategoryAction;
use App\Domains\Categories\DataTransferObjects\CreateCategoryData;
use App\Domains\Categories\DataTransferObjects\UpdateCategoryData;
use App\Domains\Categories\Http\Requests\StoreCategoryRequest;
use App\Domains\Categories\Http\Requests\UpdateCategoryRequest;
use App\Domains\Categories\Http\Resources\CategoryResource;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class CategoriesController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Categories/Index', [
            'categories' => CategoryResource::collection($this->categories->rootsForUser($request->user())),
        ]);
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $action->execute($request->user(), CreateCategoryData::fromRequest($request));

        return Redirect::route('categories.index');
    }

    public function update(UpdateCategoryRequest $request, Category $category, UpdateCategoryAction $action): RedirectResponse
    {
        $this->authorize('update', $category);

        $action->execute($category, UpdateCategoryData::fromRequest($request));

        return Redirect::route('categories.index');
    }

    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        $this->authorize('delete', $category);

        $action->execute($category);

        return Redirect::route('categories.index');
    }
}
