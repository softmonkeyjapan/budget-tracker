<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Categories\Http\Resources\CategoryResource;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Expenses\Actions\RejectExpenseAction;
use App\Domains\Expenses\Http\Resources\ExpenseResource;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class PendingExpensesController extends Controller
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Expenses/Pending', [
            'expenses' => ExpenseResource::collection($this->expenses->pendingForUser($request->user())),
            'categories' => CategoryResource::collection($this->categories->rootsForUser($request->user())),
        ]);
    }

    public function reject(Expense $expense, RejectExpenseAction $action): RedirectResponse
    {
        $this->authorize('update', $expense);

        $action->execute($expense);

        return Redirect::back();
    }
}
