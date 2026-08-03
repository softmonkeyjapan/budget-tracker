<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ComparisonService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ComparisonController extends Controller
{
    public function __construct(
        private readonly ComparisonService $comparison,
    ) {}

    public function show(Request $request): Response
    {
        $months = (int) ($request->query('months') ?? 12);
        $data = $this->comparison->forRange($request->user(), $months);

        return Inertia::render('Comparison/Index', [
            'months' => $months,
            'rows' => $data['months'],
            'incomeTotal' => $data['income_total'],
            'expenseTotal' => $data['expense_total'],
            'balanceTotal' => $data['balance_total'],
            'averageIncome' => $data['average_income'],
            'averageExpense' => $data['average_expense'],
            'bestBalanceMonth' => $data['best_balance_month'],
        ]);
    }
}
