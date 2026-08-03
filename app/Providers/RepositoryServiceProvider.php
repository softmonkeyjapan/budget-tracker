<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\CategoryRepositoryContract;
use App\Repositories\Contracts\ExpenseRepositoryContract;
use App\Repositories\Contracts\IncomeRepositoryContract;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Repositories\ExpenseRepository;
use App\Repositories\IncomeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryContract::class => UserRepository::class,
        CategoryRepositoryContract::class => CategoryRepository::class,
        ExpenseRepositoryContract::class => ExpenseRepository::class,
        IncomeRepositoryContract::class => IncomeRepository::class,
    ];
}
