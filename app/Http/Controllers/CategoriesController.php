<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class CategoriesController extends Controller
{
    public function __construct(
        private readonly CategoryService $categories,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Categories/Index', [
            'categories' => CategoryResource::collection($this->categories->treeForUser($request->user())),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);

        $this->categories->create($request->user(), $request->validated());

        return Redirect::route('categories.index');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $this->categories->update($category, $request->validated());

        return Redirect::route('categories.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->categories->delete($category);

        return Redirect::route('categories.index');
    }
}
