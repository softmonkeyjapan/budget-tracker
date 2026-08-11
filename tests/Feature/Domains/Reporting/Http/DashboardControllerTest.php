<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard page is displayed for the given month', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create(['name' => 'Alimentaire', 'color' => '#23C48E']);
    $child = Category::factory()->child($root)->create();

    Income::factory()->for($user)->create(['date' => '2026-08-03', 'amount' => 200000]);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-08-05', 'amount' => 50000]);

    $response = $this->actingAs($user)->get('/dashboard?month=2026-08');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reporting/Dashboard')
        ->where('month', '2026-08')
        ->where('incomeTotal', 200000)
        ->where('expenseTotal', 50000)
        ->where('balance', 150000)
        ->where('incomeCount', 1)
        ->where('expensePercentage', 25)
        ->has('categories', 1)
        ->where('categories.0.amount', 50000)
        ->where('categories.0.percentage', 25)
        ->has('lastExpenses', 1)
        ->has('recentIncomes', 1)
    );
});

test('dashboard handles a month with zero income without crashing', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-08-05', 'amount' => 10000]);

    $response = $this->actingAs($user)->get('/dashboard?month=2026-08');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->where('incomeTotal', 0)
        ->where('categories.0.percentage', 0)
    );
});

test('dashboard is scoped to the current user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    Income::factory()->for($otherUser)->create(['date' => '2026-08-01', 'amount' => 999999]);

    $response = $this->actingAs($user)->get('/dashboard?month=2026-08');

    $response->assertInertia(fn (Assert $page) => $page->where('incomeTotal', 0));
});
