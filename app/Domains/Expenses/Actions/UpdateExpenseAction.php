<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Actions\Concerns\ResolvesChildCategory;
use App\Domains\Expenses\DataTransferObjects\UpdateExpenseData;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Expense;
use App\Models\User;

final class UpdateExpenseAction
{
    use ResolvesChildCategory;

    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CategoryLookupInterface $categories,
    ) {}

    public function execute(User $user, Expense $expense, UpdateExpenseData $data): Expense
    {
        $category = $this->resolveChildCategory($this->categories, $user, $data->categoryId);

        return $this->expenses->update($expense, [
            'category_id' => $category->id,
            'amount' => $data->amount,
            'date' => $data->date,
            'description' => $data->description,
            'status' => ExpenseStatus::Validated,
        ]);
    }
}
