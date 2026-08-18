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
        ->has('expenses.data', 1)
        ->where('month', '2026-03')
    );
});

test('expenses index paginates results with 20 items per page by default', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->count(25)->sequence(
        fn ($sequence) => ['date' => sprintf('2026-03-%02d', $sequence->index + 1)],
    )->create();

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 20)
        ->where('expenses.meta.current_page', 1)
        ->where('expenses.meta.last_page', 2)
        ->where('expenses.meta.per_page', 20)
        ->where('expenses.meta.total', 25)
    );
});

test('expenses index returns the remaining items on page 2', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->count(25)->sequence(
        fn ($sequence) => ['date' => sprintf('2026-03-%02d', $sequence->index + 1)],
    )->create();

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&page=2');

    $response->assertInertia(fn (Assert $page) => $page
        ->has('expenses.data', 5)
        ->where('expenses.meta.current_page', 2)
    );
});

test('expenses index accepts a per_page of 50 or 100', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->count(25)->sequence(
        fn ($sequence) => ['date' => sprintf('2026-03-%02d', $sequence->index + 1)],
    )->create();

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&per_page=50');

    $response->assertInertia(fn (Assert $page) => $page
        ->has('expenses.data', 25)
        ->where('expenses.meta.per_page', 50)
        ->where('expenses.meta.last_page', 1)
    );
});

test('expenses index falls back to a per_page of 20 when the value is not allowed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&per_page=13');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('expenses.meta.per_page', 20)
    );
});

test('expenses index totals stay computed on the full filtered month, not just the current page', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create(['name' => 'Alimentaire']);
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->count(25)->sequence(
        fn ($sequence) => ['date' => sprintf('2026-03-%02d', $sequence->index + 1), 'amount' => 100],
    )->create();

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->has('expenses.data', 20)
        ->where('categoryTotals.0.amount', 2500)
    );
});

test('expenses index exposes subcategory totals with percentages for the whole month', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create(['name' => 'Alimentaire', 'color' => '#FF0000']);
    $foodChild = Category::factory()->child($root)->create(['name' => 'Supermarché']);
    $transportRoot = Category::factory()->for($user)->create(['name' => 'Transport', 'color' => '#00FF00']);
    $fuelChild = Category::factory()->child($transportRoot)->create(['name' => 'Essence']);
    Expense::factory()->for($user)->for($foodChild, 'category')->create(['date' => '2026-03-05', 'amount' => 3000]);
    Expense::factory()->for($user)->for($fuelChild, 'category')->create(['date' => '2026-03-10', 'amount' => 7000]);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('subcategoryTotals', 2)
        ->where('subcategoryTotals.0.name', 'Essence')
        ->where('subcategoryTotals.0.amount', 7000)
        ->where('subcategoryTotals.0.root_name', 'Transport')
        ->where('subcategoryTotals.0.percentage', 70)
        ->where('subcategoryTotals.1.name', 'Supermarché')
        ->where('subcategoryTotals.1.amount', 3000)
        ->where('subcategoryTotals.1.percentage', 30)
    );
});

test('subcategory totals follow the table filters, and percentages are based on the filtered subset', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create(['name' => 'Alimentaire']);
    $foodChild = Category::factory()->child($root)->create(['name' => 'Supermarché']);
    $transportRoot = Category::factory()->for($user)->create(['name' => 'Transport']);
    $fuelChild = Category::factory()->child($transportRoot)->create(['name' => 'Essence']);
    Expense::factory()->for($user)->for($foodChild, 'category')->create(['date' => '2026-03-05', 'amount' => 3000]);
    Expense::factory()->for($user)->for($fuelChild, 'category')->create(['date' => '2026-03-10', 'amount' => 7000]);

    $response = $this->actingAs($user)->get("/expenses?month=2026-03&category_id={$foodChild->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 1)
        ->has('subcategoryTotals', 1)
        ->where('subcategoryTotals.0.name', 'Supermarché')
        ->where('subcategoryTotals.0.amount', 3000)
        ->where('subcategoryTotals.0.percentage', 100)
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

test('expenses index exposes category totals grouped by root category with percentages for the whole month', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create(['name' => 'Alimentaire', 'color' => '#FF0000']);
    $foodChild = Category::factory()->child($root)->create(['name' => 'Supermarché']);
    $transportRoot = Category::factory()->for($user)->create(['name' => 'Transport', 'color' => '#00FF00']);
    $fuelChild = Category::factory()->child($transportRoot)->create(['name' => 'Essence']);
    Expense::factory()->for($user)->for($foodChild, 'category')->create(['date' => '2026-03-05', 'amount' => 3000]);
    Expense::factory()->for($user)->for($fuelChild, 'category')->create(['date' => '2026-03-10', 'amount' => 7000]);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('categoryTotals', 2)
        ->where('categoryTotals.0.name', 'Transport')
        ->where('categoryTotals.0.amount', 7000)
        ->where('categoryTotals.0.percentage', 70)
        ->where('categoryTotals.1.name', 'Alimentaire')
        ->where('categoryTotals.1.amount', 3000)
        ->where('categoryTotals.1.percentage', 30)
    );
});

test('expenses index exposes an empty category totals array when there are no expenses', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->where('categoryTotals', [])
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
        ->has('expenses.data', 1)
        ->where('expenses.data.0.category.id', $foodChild->id)
    );
});

test('expenses can be filtered by several category ids across different roots', function () {
    $user = User::factory()->create();
    $foodRoot = Category::factory()->for($user)->create(['name' => 'Alimentaire']);
    $foodChild = Category::factory()->child($foodRoot)->create();
    $transportRoot = Category::factory()->for($user)->create(['name' => 'Transport']);
    $fuelChild = Category::factory()->child($transportRoot)->create();
    $otherRoot = Category::factory()->for($user)->create(['name' => 'Loisirs']);
    $otherChild = Category::factory()->child($otherRoot)->create();
    Expense::factory()->for($user)->for($foodChild, 'category')->create(['date' => '2026-03-05']);
    Expense::factory()->for($user)->for($fuelChild, 'category')->create(['date' => '2026-03-10']);
    Expense::factory()->for($user)->for($otherChild, 'category')->create(['date' => '2026-03-15']);

    $response = $this->actingAs($user)->get("/expenses?month=2026-03&category_id[]={$foodChild->id}&category_id[]={$fuelChild->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 2)
    );
});

test('category and subcategory totals reflect a multi-id filtered selection, including an isolated child under its own root', function () {
    $user = User::factory()->create();
    $foodRoot = Category::factory()->for($user)->create(['name' => 'Alimentaire']);
    $foodChild = Category::factory()->child($foodRoot)->create(['name' => 'Supermarché']);
    $foodOtherChild = Category::factory()->child($foodRoot)->create(['name' => 'Boucherie']);
    $transportRoot = Category::factory()->for($user)->create(['name' => 'Transport']);
    $fuelChild = Category::factory()->child($transportRoot)->create(['name' => 'Essence']);
    $transportOtherChild = Category::factory()->child($transportRoot)->create(['name' => 'Métro']);
    Expense::factory()->for($user)->for($foodChild, 'category')->create(['date' => '2026-03-05', 'amount' => 3000]);
    Expense::factory()->for($user)->for($fuelChild, 'category')->create(['date' => '2026-03-10', 'amount' => 7000]);
    // Not selected: must not appear in the filtered totals.
    Expense::factory()->for($user)->for($foodOtherChild, 'category')->create(['date' => '2026-03-12', 'amount' => 5000]);
    Expense::factory()->for($user)->for($transportOtherChild, 'category')->create(['date' => '2026-03-14', 'amount' => 9000]);

    $response = $this->actingAs($user)->get("/expenses?month=2026-03&category_id[]={$foodChild->id}&category_id[]={$fuelChild->id}");

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 2)
        ->has('categoryTotals', 2)
        ->where('categoryTotals.0.name', 'Transport')
        ->where('categoryTotals.0.amount', 7000)
        ->where('categoryTotals.1.name', 'Alimentaire')
        ->where('categoryTotals.1.amount', 3000)
        ->has('subcategoryTotals', 2)
        ->where('subcategoryTotals.0.name', 'Essence')
        ->where('subcategoryTotals.1.name', 'Supermarché')
    );
});

test('filtering by another user\'s category id yields no matches for that id without erroring', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $ownChild = Category::factory()->child($root)->create();
    $otherRoot = Category::factory()->create();
    $otherChild = Category::factory()->child($otherRoot)->create();
    Expense::factory()->for($user)->for($ownChild, 'category')->create(['date' => '2026-03-05']);

    $response = $this->actingAs($user)->get("/expenses?month=2026-03&category_id[]={$ownChild->id}&category_id[]={$otherChild->id}");

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 1)
        ->where('expenses.data.0.category.id', $ownChild->id)
    );
});

test('a malformed nested category_id array value is dropped instead of being cast to id 1', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-05']);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&category_id[0][]=5');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 1)
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
        ->has('expenses.data', 1)
        ->where('expenses.data.0.description', 'Supermarché')
    );
});

test('expenses description search is case insensitive', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-05', 'description' => 'Claude']);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-10', 'description' => 'Pharmacie']);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&search=claude');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 1)
        ->where('expenses.data.0.description', 'Claude')
    );
});

test('expenses description search is accent insensitive', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-05', 'description' => 'Supermarché']);
    Expense::factory()->for($user)->for($child, 'category')->create(['date' => '2026-03-10', 'description' => 'Pharmacie']);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03&search=supermarche');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Index')
        ->has('expenses.data', 1)
        ->where('expenses.data.0.description', 'Supermarché')
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
        ->has('expenses.data', 1)
        ->where('expenses.data.0.date', '2026-03-10')
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
        ->where('expenses.data.0.date', '2026-03-20')
        ->where('expenses.data.1.date', '2026-03-05')
    );
});

test('expenses created later on the same day sort above earlier ones', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $earlier = Expense::factory()->for($user)->for($child, 'category')->create([
        'date' => '2026-03-15',
        'created_at' => '2026-03-15 09:00:00',
    ]);
    $later = Expense::factory()->for($user)->for($child, 'category')->create([
        'date' => '2026-03-15',
        'created_at' => '2026-03-15 12:00:00',
    ]);

    $response = $this->actingAs($user)->get('/expenses?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('expenses.data.0.id', $later->id)
        ->where('expenses.data.1.id', $earlier->id)
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
        ->where('expenses.data.0.amount', 1000)
        ->where('expenses.data.1.amount', 5000)
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
