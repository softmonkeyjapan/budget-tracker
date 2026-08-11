<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('comparison page aggregates income and expense per month', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();

    $currentMonth = now()->startOfMonth();
    $previousMonth = $currentMonth->copy()->subMonth();

    Income::factory()->for($user)->create(['date' => $currentMonth->copy()->addDays(4), 'amount' => 100000]);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => $currentMonth->copy()->addDays(2), 'amount' => 30000]);
    Income::factory()->for($user)->create(['date' => $previousMonth->copy()->addDays(4), 'amount' => 80000]);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => $previousMonth->copy()->addDays(2), 'amount' => 20000]);

    $response = $this->actingAs($user)->get('/comparison?months=2');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reporting/Comparison/Index')
        ->where('months', 2)
        ->has('rows', 2)
        ->where('rows.1.month', $currentMonth->format('Y-m'))
        ->where('rows.1.income', 100000)
        ->where('rows.1.expense', 30000)
        ->where('rows.1.balance', 70000)
        ->where('rows.0.month', $previousMonth->format('Y-m'))
        ->where('incomeTotal', 180000)
        ->where('expenseTotal', 50000)
        ->where('balanceTotal', 130000)
    );
});

test('comparison defaults to 12 months', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/comparison');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('months', 12)
        ->has('rows', 12)
    );
});

test('comparison is scoped to the current user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Income::factory()->for($otherUser)->create(['date' => now(), 'amount' => 999999]);

    $response = $this->actingAs($user)->get('/comparison?months=1');

    $response->assertInertia(fn (Assert $page) => $page->where('incomeTotal', 0));
});
