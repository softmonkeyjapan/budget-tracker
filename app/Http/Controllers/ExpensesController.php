<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Services\CategoryService;
use App\Services\ExpenseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class ExpensesController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenses,
        private readonly CategoryService $categories,
    ) {}

    public function index(Request $request): Response
    {
        $month = $request->query('month') ?? now()->format('Y-m');

        $filters = [
            'category_id' => $request->integer('category_id') ?: null,
            'search' => $request->string('search')->trim()->value() ?: null,
            'date' => $request->query('date') ?: null,
        ];

        $sortBy = in_array($request->query('sort'), ['date', 'category', 'description', 'amount'], true)
            ? $request->query('sort')
            : 'date';

        $sortDirection = $request->query('direction') === 'asc' ? 'asc' : 'desc';

        return Inertia::render('Expenses/Index', [
            'expenses' => ExpenseResource::collection(
                $this->expenses->forMonth($request->user(), $month, $filters, $sortBy, $sortDirection),
            ),
            'categories' => CategoryResource::collection($this->categories->treeForUser($request->user())),
            'month' => $month,
            'filters' => [
                ...$filters,
                'sort' => $sortBy,
                'direction' => $sortDirection,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Expense::class);

        return Inertia::render('Expenses/Create', [
            'categories' => CategoryResource::collection($this->categories->treeForUser($request->user())),
            'month' => $request->query('month') ?? now()->format('Y-m'),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $this->expenses->create($request->user(), $request->validated());

        return Redirect::route('expenses.index', ['month' => $expense->date->format('Y-m')]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->authorize('update', $expense);

        $this->expenses->update($request->user(), $expense, $request->validated());

        return Redirect::back();
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $this->expenses->delete($expense);

        return Redirect::back();
    }
}
