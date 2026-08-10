<?php

use App\Domains\Incomes\Actions\UpdateIncomeAction;
use App\Domains\Incomes\DataTransferObjects\UpdateIncomeData;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\Income;

it('updates an income with the given data', function () {
    $income = Income::factory()->make(['user_id' => 1]);

    $incomes = Mockery::mock(IncomeRepositoryInterface::class);
    $incomes->shouldReceive('update')
        ->once()
        ->with($income, [
            'amount' => 5000,
            'date' => '2026-08-10',
            'description' => 'Mise à jour',
        ])
        ->andReturn($income);

    $action = new UpdateIncomeAction($incomes);

    $result = $action->execute($income, new UpdateIncomeData(
        amount: 5000,
        date: '2026-08-10',
        description: 'Mise à jour',
    ));

    expect($result)->toBe($income);
});
