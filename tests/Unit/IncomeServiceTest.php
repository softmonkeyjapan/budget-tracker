<?php

use App\Models\User;
use App\Repositories\Contracts\IncomeRepositoryContract;
use App\Services\IncomeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

test('paginateForMonth passes an allowed per_page through to the repository', function () {
    $user = User::factory()->make(['id' => 1]);

    $paginator = Mockery::mock(LengthAwarePaginator::class);

    $incomes = Mockery::mock(IncomeRepositoryContract::class);
    $incomes->shouldReceive('paginateForUserAndMonth')
        ->once()
        ->with($user, '2026-08', 50, 2)
        ->andReturn($paginator);

    $service = new IncomeService($incomes);

    expect($service->paginateForMonth($user, '2026-08', 50, 2))->toBe($paginator);
});

test('paginateForMonth clamps a per_page that is not 20, 50 or 100 down to 20', function () {
    $user = User::factory()->make(['id' => 1]);

    $paginator = Mockery::mock(LengthAwarePaginator::class);

    $incomes = Mockery::mock(IncomeRepositoryContract::class);
    $incomes->shouldReceive('paginateForUserAndMonth')
        ->once()
        ->with($user, '2026-08', 20, 1)
        ->andReturn($paginator);

    $service = new IncomeService($incomes);

    $service->paginateForMonth($user, '2026-08', 13, 1);
});
