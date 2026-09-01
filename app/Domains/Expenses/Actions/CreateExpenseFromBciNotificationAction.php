<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Expense;

final class CreateExpenseFromBciNotificationAction
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly ParseBciNotificationAction $parse,
        private readonly ConvertAmountToXpfAction $convert,
    ) {}

    public function execute(int $userId, string $rawBody): Expense
    {
        $parsed = $this->parse->execute($rawBody);

        if ($parsed === null) {
            return $this->expenses->create([
                'user_id' => $userId,
                'category_id' => null,
                'amount' => null,
                'date' => now()->toDateString(),
                'description' => null,
                'status' => ExpenseStatus::Rejected,
                'raw_payload' => $rawBody,
            ]);
        }

        return $this->expenses->create([
            'user_id' => $userId,
            'category_id' => null,
            'amount' => $this->convert->execute($parsed->amount, $parsed->currency),
            'date' => now()->toDateString(),
            'description' => $parsed->merchant,
            'status' => ExpenseStatus::Draft,
            'raw_payload' => $rawBody,
        ]);
    }
}
