<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Providers;

use App\Domains\Expenses\Adapters\FrankfurterExchangeRateProvider;
use App\Domains\Expenses\Contracts\ExchangeRateProviderInterface;
use App\Domains\Expenses\Policies\EcheanceOccurrencePolicy;
use App\Domains\Expenses\Policies\EcheancePolicy;
use App\Domains\Expenses\Policies\ExpensePolicy;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Expenses\Repositories\EloquentEcheanceRepository;
use App\Domains\Expenses\Repositories\EloquentExpenseRepository;
use App\Domains\Shared\Contracts\ExpenseExistenceInterface;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
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
        EcheanceRepositoryInterface::class => EloquentEcheanceRepository::class,
    ];

    public function boot(): void
    {
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Echeance::class, EcheancePolicy::class);
        Gate::policy(EcheanceOccurrence::class, EcheanceOccurrencePolicy::class);
    }
}
