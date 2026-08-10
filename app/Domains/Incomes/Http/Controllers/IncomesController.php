<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Http\Controllers;

use App\Domains\Incomes\Actions\CreateIncomeAction;
use App\Domains\Incomes\Actions\DeleteIncomeAction;
use App\Domains\Incomes\Actions\UpdateIncomeAction;
use App\Domains\Incomes\DataTransferObjects\CreateIncomeData;
use App\Domains\Incomes\DataTransferObjects\UpdateIncomeData;
use App\Domains\Incomes\Http\Requests\StoreIncomeRequest;
use App\Domains\Incomes\Http\Requests\UpdateIncomeRequest;
use App\Domains\Incomes\Http\Resources\IncomeResource;
use App\Domains\Incomes\Repositories\Contracts\IncomeRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class IncomesController extends Controller
{
    /**
     * @var array<int, int>
     */
    private const ALLOWED_PER_PAGE = [20, 50, 100];

    public function __construct(
        private readonly IncomeRepositoryInterface $incomes,
    ) {}

    public function index(Request $request): Response
    {
        $month = $request->query('month') ?? now()->format('Y-m');
        $perPage = $request->integer('per_page', 20);
        $perPage = in_array($perPage, self::ALLOWED_PER_PAGE, true) ? $perPage : 20;

        $incomes = $this->incomes->paginateForUserAndMonth(
            $request->user(),
            $month,
            $perPage,
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
            'recentIncomes' => IncomeResource::collection($this->incomes->forUserAndMonth($request->user(), $month)),
        ]);
    }

    public function store(StoreIncomeRequest $request, CreateIncomeAction $action): RedirectResponse
    {
        $this->authorize('create', Income::class);

        $income = $action->execute($request->user(), CreateIncomeData::fromRequest($request));

        return Redirect::route('incomes.index', ['month' => $income->date->format('Y-m')]);
    }

    public function update(UpdateIncomeRequest $request, Income $income, UpdateIncomeAction $action): RedirectResponse
    {
        $this->authorize('update', $income);

        $action->execute($income, UpdateIncomeData::fromRequest($request));

        return Redirect::back();
    }

    public function destroy(Income $income, DeleteIncomeAction $action): RedirectResponse
    {
        $this->authorize('delete', $income);

        $action->execute($income);

        return Redirect::back();
    }
}
