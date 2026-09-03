<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Controllers;

use App\Domains\Categories\Http\Resources\CategoryResource;
use App\Domains\Categories\Repositories\Contracts\CategoryRepositoryInterface;
use App\Domains\Expenses\Actions\CancelEcheanceAction;
use App\Domains\Expenses\Actions\CreateEcheanceAction;
use App\Domains\Expenses\DataTransferObjects\CreateEcheanceData;
use App\Domains\Expenses\Http\Requests\StoreEcheanceRequest;
use App\Domains\Expenses\Http\Resources\EcheanceResource;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\Echeance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class EcheancesController extends Controller
{
    public function __construct(
        private readonly EcheanceRepositoryInterface $echeances,
        private readonly CategoryRepositoryInterface $categories,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Expenses/Echeances/Index', [
            'echeances' => EcheanceResource::collection($this->echeances->forUser($request->user())),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Echeance::class);

        return Inertia::render('Expenses/Echeances/Create', [
            'categories' => CategoryResource::collection($this->categories->rootsForUser($request->user())),
        ]);
    }

    public function store(StoreEcheanceRequest $request, CreateEcheanceAction $action): RedirectResponse
    {
        $this->authorize('create', Echeance::class);

        $action->execute($request->user(), CreateEcheanceData::fromRequest($request));

        return Redirect::route('echeances.index');
    }

    public function cancel(Echeance $echeance, CancelEcheanceAction $action): RedirectResponse
    {
        $this->authorize('update', $echeance);

        $action->execute($echeance);

        return Redirect::back();
    }
}
