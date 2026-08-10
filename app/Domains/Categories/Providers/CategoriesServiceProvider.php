<?php

declare(strict_types=1);

namespace App\Domains\Categories\Providers;

use App\Domains\Categories\Policies\CategoryPolicy;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Categories\Repositories\EloquentCategoryRepository;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class CategoriesServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        CategoryRepositoryInterface::class => EloquentCategoryRepository::class,
        CategoryLookupInterface::class => EloquentCategoryRepository::class,
    ];

    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
    }
}
