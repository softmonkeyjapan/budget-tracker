<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Providers;

use App\Domains\Incomes\Policies\IncomePolicy;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Domains\Incomes\Repositories\EloquentIncomeRepository;
use App\Models\Income;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class IncomesServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        IncomeRepositoryInterface::class => EloquentIncomeRepository::class,
    ];

    public function boot(): void
    {
        Gate::policy(Income::class, IncomePolicy::class);
    }
}
