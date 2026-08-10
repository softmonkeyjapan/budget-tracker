<?php

use App\Domains\Incomes\Actions\CreateIncomeAction;
use App\Domains\Incomes\DataTransferObjects\CreateIncomeData;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\Income;
use App\Models\User;

it('creates an income scoped to the given user', function () {
    $user = User::factory()->make(['id' => 1]);
    $income = Income::factory()->make(['user_id' => 1, 'amount' => 230000]);

    $incomes = Mockery::mock(IncomeRepositoryInterface::class);
    $incomes->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 1,
            'amount' => 230000,
            'date' => '2026-08-03',
            'description' => 'Salaire août 2026',
        ])
        ->andReturn($income);

    $action = new CreateIncomeAction($incomes);

    $result = $action->execute($user, new CreateIncomeData(
        amount: 230000,
        date: '2026-08-03',
        description: 'Salaire août 2026',
    ));

    expect($result)->toBe($income);
});
