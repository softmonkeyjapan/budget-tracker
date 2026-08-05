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

test('expenses index exposes subcategory totals for the chart, independent of table filters', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create(['name' => 'Alimentaire', 'color' => '#FF0000']);
    $foodChild = Category::factory()->child($root)->create(['name' => 'Supermarché']);
    $transportRoot = Category::factory()->for($user)->create(['name' => 'Transport', 'color' => '#00FF00']);
    $fuelChild = Category::factory()->child($transportRoot)->create(['name' => 'Essence']);
    Expense::factory()->for($user)->for($foodChild, 'category')->create(['date' => '2026-03-05', 'amount' => 3000]);
    Expense::factory()->for($user)->for($fuelChild, 'category')->create(['date' => '2026-03-10', 'amount' => 7000]);

    $response = $this->actingAs($user)->get("/expenses?month=2026-03&category_id={$foodChild->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses', 1)
        ->has('subcategoryTotals', 2)
        ->where('subcategoryTotals.0.name', 'Essence')
        ->where('subcategoryTotals.0.amount', 7000)
        ->where('subcategoryTotals.0.root_name', 'Transport')
        ->where('subcategoryTotals.1.name', 'Supermarché')
        ->where('subcategoryTotals.1.amount', 3000)
    );
});

test('expenses index exposes an empty subcategory totals array when there are no expenses', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->where('subcategoryTotals', [])
    );
});

test('expenses can be filtered by category', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $foodChild = Category::factory()->child($root)->create();
    $transportChild = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($foodChild, 'category')->create(['date' => '2026-03-05']);
    Expense::factory()->for($user)->for($transportChild, 'category')->create(['date' => '2026-03-10']);

    $response = $this->actingAs($user)->get("/expenses?month=2026-03&category_id={$foodChild->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses', 1)
        ->where('expenses.0.category.id', $foodChild->id)
    );
});

test('expenses can be filtered by a description search', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-05', 'description' => 'Supermarché']);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-10', 'description' => 'Pharmacie']);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&search=super');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses', 1)
        ->where('expenses.0.description', 'Supermarché')
    );
});

test('expenses can be filtered by an exact date', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-05']);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-10']);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&date=2026-03-10');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses', 1)
        ->where('expenses.0.date', '2026-03-10')
    );
});

test('expenses default to sorting by date descending', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-05']);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-20']);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('expenses.0.date', '2026-03-20')
        ->where('expenses.1.date', '2026-03-05')
    );
});

test('expenses can be sorted by amount ascending', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-05', 'amount' => 5000]);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-10', 'amount' => 1000]);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&sort=amount&direction=asc');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('expenses.0.amount', 1000)
        ->where('expenses.1.amount', 5000)
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
