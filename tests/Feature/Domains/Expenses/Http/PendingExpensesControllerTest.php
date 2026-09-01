<?php

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the pending page lists only draft and rejected expenses', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create();
    $draft = Expense::factory()->for($user)->draft()->create();
    $rejected = Expense::factory()->for($user)->rejected()->create();

    $response = $this->actingAs($user)->get('/expenses/pending');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Pending')
        ->has('expenses', 2)
    );
    $this->assertTrue(collect(['brouillon', 'rejetee'])->contains($draft->fresh()->status->value));
    $this->assertTrue(collect(['brouillon', 'rejetee'])->contains($rejected->fresh()->status->value));
});

test('validating a draft assigns a category and marks it validated', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $draft = Expense::factory()->for($user)->draft()->create(['amount' => 5000, 'description' => 'NETFLIX.COM']);

    $response = $this->actingAs($user)->put("/expenses/{$draft->id}", [
        'category_id' => $child->id,
        'amount' => 5000,
        'date' => $draft->date->toDateString(),
        'description' => 'Netflix',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('expenses', [
        'id' => $draft->id,
        'status' => 'validee',
        'category_id' => $child->id,
    ]);
});

test('rejecting a draft expense marks it as rejected', function () {
    $user = User::factory()->create();
    $draft = Expense::factory()->for($user)->draft()->create();

    $response = $this->actingAs($user)->patch("/expenses/{$draft->id}/reject");

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('expenses', [
        'id' => $draft->id,
        'status' => 'rejetee',
    ]);
});

test('rejecting an already validated expense fails', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $expense = Expense::factory()->for($user)->for($child, 'category')->create();

    $response = $this->actingAs($user)->patch("/expenses/{$expense->id}/reject");

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'status' => ExpenseStatus::Validated->value,
    ]);
});

test('rejecting another user\'s expense is forbidden', function () {
    $user = User::factory()->create();
    $draft = Expense::factory()->draft()->create();

    $response = $this->actingAs($user)->patch("/expenses/{$draft->id}/reject");

    $response->assertForbidden();
});
