<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Actions\Concerns\ResolvesChildCategory;
use App\Domains\Expenses\DataTransferObjects\CreateExpenseData;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Expense;
use App\Models\User;

final class CreateExpenseAction
{
    use ResolvesChildCategory;

    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CategoryLookupInterface $categories,
    ) {}

    public function execute(User $user, CreateExpenseData $data): Expense
    {
        $category = $this->resolveChildCategory($this->categories, $user, $data->categoryId);

        return $this->expenses->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => $data->amount,
            'date' => $data->date,
            'description' => $data->description,
            'status' => ExpenseStatus::Validated,
        ]);
    }
}
