<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Categories\Http\Resources\CategoryResource;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Expenses\Actions\CreateExpenseAction;
use App\Domains\Expenses\Actions\DeleteExpenseAction;
use App\Domains\Expenses\Actions\UpdateExpenseAction;
use App\Domains\Expenses\DataTransferObjects\CreateExpenseData;
use App\Domains\Expenses\DataTransferObjects\UpdateExpenseData;
use App\Domains\Expenses\Http\Requests\StoreExpenseRequest;
use App\Domains\Expenses\Http\Requests\UpdateExpenseRequest;
use App\Domains\Expenses\Http\Resources\ExpenseResource;
use App\Domains\Expenses\Queries\GetCategoryTotalsForMonthQuery;
use App\Domains\Expenses\Queries\GetSubcategoryTotalsForMonthQuery;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class ExpensesController extends Controller
{
    /**
     * @var array<int, int>
     */
    private const ALLOWED_PER_PAGE = [20, 50, 100];

    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function index(
        Request $request,
        GetCategoryTotalsForMonthQuery $categoryTotals,
        GetSubcategoryTotalsForMonthQuery $subcategoryTotals,
    ): Response {
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

        $perPage = $request->integer('per_page', 20);
        $perPage = in_array($perPage, self::ALLOWED_PER_PAGE, true) ? $perPage : 20;

        $expenses = $this->expenses->paginateForUserAndMonth(
            $request->user(),
            $month,
            $filters,
            $sortBy,
            $sortDirection,
            $perPage,
            max(1, $request->integer('page', 1)),
        );

        return Inertia::render('Expenses/Index', [
            'expenses' => [
                'data' => ExpenseResource::collection($expenses->getCollection()),
                'meta' => [
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                    'per_page' => $expenses->perPage(),
                    'total' => $expenses->total(),
                ],
            ],
            'categories' => CategoryResource::collection($this->categories->rootsForUser($request->user())),
            'categoryTotals' => $categoryTotals->execute($request->user(), $month, $filters),
            'subcategoryTotals' => $subcategoryTotals->execute($request->user(), $month, $filters),
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
            'categories' => CategoryResource::collection($this->categories->rootsForUser($request->user())),
            'month' => $request->query('month') ?? now()->format('Y-m'),
        ]);
    }

    public function store(StoreExpenseRequest $request, CreateExpenseAction $action): RedirectResponse
    {
        $this->authorize('create', Expense::class);

        $expense = $action->execute($request->user(), CreateExpenseData::fromRequest($request));

        return Redirect::route('expenses.index', ['month' => $expense->date->format('Y-m')]);
    }

    public function update(UpdateExpenseRequest $request, Expense $expense, UpdateExpenseAction $action): RedirectResponse
    {
        $this->authorize('update', $expense);

        $action->execute($request->user(), $expense, UpdateExpenseData::fromRequest($request));

        return Redirect::back();
    }

    public function destroy(Expense $expense, DeleteExpenseAction $action): RedirectResponse
    {
        $this->authorize('delete', $expense);

        $action->execute($expense);

        return Redirect::back();
    }
}
