<?php

use App\Models\Income;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('incomes index page is displayed for the given month', function () {
    $user = User::factory()->create();
    Income::factory()->for($user)->create(['date' => '2026-03-15']);
    Income::factory()->for($user)->create(['date' => '2026-04-01']);

    $response = $this->actingAs($user)->get('/incomes?month=2026-03');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Incomes/Index')
        ->has('incomes.data', 1)
        ->where('month', '2026-03')
    );
});

test('incomes index paginates results with 20 items per page by default', function () {
    $user = User::factory()->create();
    Income::factory()->for($user)->count(25)->sequence(
        fn ($sequence) => ['date' => sprintf('2026-03-%02d', $sequence->index + 1)],
    )->create();

    $response = $this->actingAs($user)->get('/incomes?month=2026-03');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Incomes/Index')
        ->has('incomes.data', 20)
        ->where('incomes.meta.current_page', 1)
        ->where('incomes.meta.last_page', 2)
        ->where('incomes.meta.per_page', 20)
        ->where('incomes.meta.total', 25)
    );
});

test('incomes index returns the remaining items on page 2', function () {
    $user = User::factory()->create();
    Income::factory()->for($user)->count(25)->sequence(
        fn ($sequence) => ['date' => sprintf('2026-03-%02d', $sequence->index + 1)],
    )->create();

    $response = $this->actingAs($user)->get('/incomes?month=2026-03&page=2');

    $response->assertInertia(fn (Assert $page) => $page
        ->has('incomes.data', 5)
        ->where('incomes.meta.current_page', 2)
    );
});

test('incomes index accepts a per_page of 50 or 100', function () {
    $user = User::factory()->create();
    Income::factory()->for($user)->count(25)->sequence(
        fn ($sequence) => ['date' => sprintf('2026-03-%02d', $sequence->index + 1)],
    )->create();

    $response = $this->actingAs($user)->get('/incomes?month=2026-03&per_page=50');

    $response->assertInertia(fn (Assert $page) => $page
        ->has('incomes.data', 25)
        ->where('incomes.meta.per_page', 50)
        ->where('incomes.meta.last_page', 1)
    );
});

test('incomes index falls back to a per_page of 20 when the value is not allowed', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/incomes?month=2026-03&per_page=13');

    $response->assertInertia(fn (Assert $page) => $page
        ->where('incomes.meta.per_page', 20)
    );
});

test('an income entry can be created', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/incomes', [
        'amount' => 230000,
        'date' => '2026-08-03',
        'description' => 'Salaire août 2026',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/incomes?month=2026-08');

    $this->assertDatabaseHas('incomes', [
        'user_id' => $user->id,
        'amount' => 230000,
        'description' => 'Salaire août 2026',
    ]);
});

test('multiple income entries can exist in the same month', function () {
    $user = User::factory()->create();
    Income::factory()->for($user)->create(['date' => '2026-08-03']);
    Income::factory()->for($user)->create(['date' => '2026-08-21']);

    $response = $this->actingAs($user)->get('/incomes?month=2026-08');

    $response->assertInertia(fn (Assert $page) => $page->has('incomes.data', 2));
});

test('an income entry can be updated by its owner', function () {
    $user = User::factory()->create();
    $income = Income::factory()->for($user)->create();

    $response = $this->actingAs($user)->put("/incomes/{$income->id}", [
        'amount' => 5000,
        'date' => '2026-08-10',
        'description' => 'Mise à jour',
    ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('incomes', [
        'id' => $income->id,
        'amount' => 5000,
        'description' => 'Mise à jour',
    ]);
});

test('an income entry cannot be updated by another user', function () {
    $user = User::factory()->create();
    $income = Income::factory()->create();

    $response = $this->actingAs($user)->put("/incomes/{$income->id}", [
        'amount' => 1,
        'date' => '2026-08-10',
    ]);

    $response->assertForbidden();
});

test('an income entry can be deleted by its owner', function () {
    $user = User::factory()->create();
    $income = Income::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete("/incomes/{$income->id}");

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('incomes', ['id' => $income->id]);
});

test('an income entry cannot be deleted by another user', function () {
    $user = User::factory()->create();
    $income = Income::factory()->create();

    $response = $this->actingAs($user)->delete("/incomes/{$income->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('incomes', ['id' => $income->id]);
});
