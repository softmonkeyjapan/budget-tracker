<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Actions;

use App\Domains\Incomes\DataTransferObjects\UpdateIncomeData;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Models\Income;

final class UpdateIncomeAction
{
    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
    ) {}

    public function execute(Income $income, UpdateIncomeData $data): Income
    {
        return $this->incomes->update($income, [
            'amount' => $data->amount,
            'date' => $data->date,
            'description' => $data->description,
        ]);
    }
}
