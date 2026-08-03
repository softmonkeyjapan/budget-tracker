<?php

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('expenses index page is displayed for the given month', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-15']);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-04-01']);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses', 1)
        ->where('month', '2026-03')
    );
});

test('an expense can be created under a child category', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();

    $response = $this->actingAs($user)->post('/expenses', [
        'category_id' => $child->id,
        'amount' => 14850,
        'date' => '2026-08-05',
        'description' => 'Supermarché',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/expenses?month=2026-08');

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'category_id' => $child->id,
        'amount' => 14850,
    ]);
});

test('an expense cannot be created directly under a root category', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->post('/expenses', [
        'category_id' => $root->id,
        'amount' => 1000,
        'date' => '2026-08-05',
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseMissing('expenses', ['category_id' => $root->id]);
});

test('an expense cannot be created under another user\'s category', function () {
    $user = User::factory()->create();
    $otherRoot = Category::factory()->create();
    $otherChild = Category::factory()->child($otherRoot)->create();

    $response = $this->actingAs($user)->post('/expenses', [
        'category_id' => $otherChild->id,
        'amount' => 1000,
        'date' => '2026-08-05',
    ]);

    $response->assertSessionHasErrors('category_id');
});

test('an expense can be updated by its owner', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $expense = Expense::factory()->for($user)->for($child, 'category')->create();

    $response = $this->actingAs($user)->put("/expenses/{$expense->id}", [
        'category_id' => $child->id,
        'amount' => 5000,
        'date' => '2026-08-10',
        'description' => 'Mise à jour',
    ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('expenses', [
        'id' => $expense->id,
        'amount' => 5000,
        'description' => 'Mise à jour',
    ]);
});

test('an expense cannot be updated by another user', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $ownChild = Category::factory()->child($root)->create();
    $expense = Expense::factory()->create();

    $response = $this->actingAs($user)->put("/expenses/{$expense->id}", [
        'category_id' => $ownChild->id,
        'amount' => 1,
        'date' => '2026-08-10',
    ]);

    $response->assertForbidden();
});

test('an expense can be deleted by its owner', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $expense = Expense::factory()->for($user)->for($child, 'category')->create();

    $response = $this->actingAs($user)->delete("/expenses/{$expense->id}");

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
});

test('an expense cannot be deleted by another user', function () {
    $user = User::factory()->create();
    $expense = Expense::factory()->create();

    $response = $this->actingAs($user)->delete("/expenses/{$expense->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
});
