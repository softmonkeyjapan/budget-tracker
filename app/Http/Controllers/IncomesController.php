<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreIncomeRequest;
use App\Http\Requests\UpdateIncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use App\Services\IncomeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class IncomesController extends Controller
{
    public function __construct(
        private readonly IncomeService $incomes,
    ) {}

    public function index(Request $request): Response
    {
        $month = $request->query('month') ?? now()->format('Y-m');

        $incomes = $this->incomes->paginateForMonth(
            $request->user(),
            $month,
            $request->integer('per_page', 20),
            max(1, $request->integer('page', 1)),
        );

        return Inertia::render('Incomes/Index', [
            'incomes' => [
                'data' => IncomeResource::collection($incomes->getCollection()),
                'meta' => [
                    'current_page' => $incomes->currentPage(),
                    'last_page' => $incomes->lastPage(),
                    'per_page' => $incomes->perPage(),
                    'total' => $incomes->total(),
                ],
            ],
            'month' => $month,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Income::class);

        $month = $request->query('month') ?? now()->format('Y-m');

        return Inertia::render('Incomes/Create', [
            'month' => $month,
            'recentIncomes' => IncomeResource::collection($this->incomes->forMonth($request->user(), $month)),
        ]);
    }

    public function store(StoreIncomeRequest $request): RedirectResponse
    {
        $this->authorize('create', Income::class);

        $income = $this->incomes->create($request->user(), $request->validated());

        return Redirect::route('incomes.index', ['month' => $income->date->format('Y-m')]);
    }

    public function update(UpdateIncomeRequest $request, Income $income): RedirectResponse
    {
        $this->authorize('update', $income);

        $this->incomes->update($income, $request->validated());

        return Redirect::back();
    }

    public function destroy(Income $income): RedirectResponse
    {
        $this->authorize('delete', $income);

        $this->incomes->delete($income);

        return Redirect::back();
    }
}
