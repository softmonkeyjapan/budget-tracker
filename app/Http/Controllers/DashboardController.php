<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ExpenseResource;
use App\Http\Resources\IncomeResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
    ) {}

    public function show(Request $request): Response
    {
        $month = $request->query('month') ?? now()->format('Y-m');

        $data = $this->dashboard->forMonth($request->user(), $month);

        return Inertia::render('Dashboard', [
            'month' => $month,
            'incomeTotal' => $data['income_total'],
            'expenseTotal' => $data['expense_total'],
            'balance' => $data['balance'],
            'incomeCount' => $data['income_count'],
            'expensePercentage' => $data['expense_percentage'],
            'categories' => $data['categories'],
            'unspentPercentage' => $data['unspent_percentage'],
            'lastExpenses' => ExpenseResource::collection($data['last_expenses']),
            'recentIncomes' => IncomeResource::collection($data['recent_incomes']),
        ]);
    }
}
