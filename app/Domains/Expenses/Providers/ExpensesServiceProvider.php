<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Providers;

use App\Domains\Expenses\Adapters\FrankfurterExchangeRateProvider;
use App\Domains\Expenses\Contracts\ExchangeRateProviderInterface;
use App\Domains\Expenses\Policies\ExpensePolicy;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Expenses\Repositories\EloquentExpenseRepository;
use App\Domains\Shared\Contracts\ExpenseExistenceInterface;
use App\Models\Expense;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class ExpensesServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        ExpenseRepositoryInterface::class => EloquentExpenseRepository::class,
        ExpenseExistenceInterface::class => EloquentExpenseRepository::class,
        ExchangeRateProviderInterface::class => FrankfurterExchangeRateProvider::class,
    ];

    public function boot(): void
    {
        Gate::policy(Expense::class, ExpensePolicy::class);
    }
}
