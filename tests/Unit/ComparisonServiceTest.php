<?php

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use App\Repositories\Contracts\ExpenseRepositoryContract;
use App\Repositories\Contracts\IncomeRepositoryContract;
use App\Services\ComparisonService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

test('forRange aggregates totals and zero-fills months without data', function () {
    $user = User::factory()->make(['id' => 1]);

    $now = Carbon::now()->startOfMonth();
    $currentMonth = $now->format('Y-m');
    $previousMonth = $now->copy()->subMonth()->format('Y-m');

    $incomes = Mockery::mock(IncomeRepositoryContract::class);
    $incomes->shouldReceive('forUserAndDateRange')->once()->andReturn(new Collection([
        Income::factory()->make(['user_id' => 1, 'amount' => 100000, 'date' => $now->copy()->addDays(4)]),
    ]));

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndDateRange')->once()->andReturn(new Collection([
        Expense::factory()->make(['user_id' => 1, 'category_id' => 1, 'amount' => 40000, 'date' => $now->copy()->addDays(2)]),
    ]));

    $service = new ComparisonService($expenses, $incomes);

    $result = $service->forRange($user, 2);

    expect($result['months'])->toHaveCount(2);
    expect($result['months'][0]['month'])->toBe($previousMonth);
    expect($result['months'][0]['income'])->toBe(0);
    expect($result['months'][1]['month'])->toBe($currentMonth);
    expect($result['months'][1]['income'])->toBe(100000);
    expect($result['months'][1]['expense'])->toBe(40000);
    expect($result['months'][1]['balance'])->toBe(60000);
    expect($result['income_total'])->toBe(100000);
    expect($result['expense_total'])->toBe(40000);
    expect($result['balance_total'])->toBe(60000);
    expect($result['average_income'])->toBe(50000);
    expect($result['best_balance_month']['month'])->toBe($currentMonth);
});

test('months is clamped between 1 and 24', function () {
    $user = User::factory()->make(['id' => 1]);

    $incomes = Mockery::mock(IncomeRepositoryContract::class);
    $incomes->shouldReceive('forUserAndDateRange')->andReturn(new Collection([]));

    $expenses = Mockery::mock(ExpenseRepositoryContract::class);
    $expenses->shouldReceive('forUserAndDateRange')->andReturn(new Collection([]));

    $service = new ComparisonService($expenses, $incomes);

    expect($service->forRange($user, 0)['months'])->toHaveCount(1);
    expect($service->forRange($user, 999)['months'])->toHaveCount(24);
});
