<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Actions;

use App\Domains\Incomes\DataTransferObjects\CreateIncomeData;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\Income;
use App\Models\User;

final class CreateIncomeAction
{
    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
    ) {}

    public function execute(User $user, CreateIncomeData $data): Income
    {
        return $this->incomes->create([
            'user_id' => $user->id,
            'amount' => $data->amount,
            'date' => $data->date,
            'description' => $data->description,
        ]);
    }
}
