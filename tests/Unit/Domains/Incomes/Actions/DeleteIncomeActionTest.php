<?php

use App\Domains\Incomes\Actions\DeleteIncomeAction;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\Income;

it('deletes the given income', function () {
    $income = Income::factory()->make(['user_id' => 1]);

    $incomes = Mockery::mock(IncomeRepositoryInterface::class);
    $incomes->shouldReceive('delete')->once()->with($income);

    $action = new DeleteIncomeAction($incomes);

    $action->execute($income);
});
