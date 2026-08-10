<?php

declare(strict_types=1);

namespace App\Domains\Reporting\Http\Controllers;

use App\Domains\Expenses\Http\Resources\ExpenseResource;
use App\Domains\Incomes\Http\Resources\IncomeResource;
use App\Domains\Reporting\Queries\GetDashboardSummaryQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly GetDashboardSummaryQuery $dashboard,
    ) {}

    public function show(Request $request): Response
    {
        $month = $request->query('month') ?? now()->format('Y-m');

        $data = $this->dashboard->execute($request->user(), $month);

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
