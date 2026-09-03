<?php

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Models\Category;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('échéances index page lists the user\'s échéances with their occurrences', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $echeance = Echeance::factory()->for($user)->for($child, 'category')->create(['description' => 'Assurance véhicule']);
    EcheanceOccurrence::factory()->for($echeance)->create(['date' => '2026-09-03']);

    $response = $this->actingAs($user)->get('/echeances');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Expenses/Echeances/Index')
        ->has('echeances', 1)
        ->where('echeances.0.description', 'Assurance véhicule')
        ->has('echeances.0.occurrences', 1)
    );
});

test('échéances index only lists échéances owned by the user', function () {
    $user = User::factory()->create();
    Echeance::factory()->create();

    $response = $this->actingAs($user)->get('/echeances');

    $response->assertInertia(fn (Assert $page) => $page->has('echeances', 0));
});

test('a finite échéancier can be created with one line per occurrence', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();

    $response = $this->actingAs($user)->post('/echeances', [
        'category_id' => $child->id,
        'description' => 'Assurance véhicule',
        'frequency' => 'mensuelle',
        'occurrences_total' => 3,
        'occurrences' => [
            ['date' => '2026-09-03', 'amount' => 15000],
            ['date' => '2026-10-03', 'amount' => 15000],
            ['date' => '2026-11-03', 'amount' => 12000],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect('/echeances');

    $this->assertDatabaseHas('echeances', [
        'user_id' => $user->id,
        'category_id' => $child->id,
        'description' => 'Assurance véhicule',
        'occurrences_total' => 3,
    ]);
    $this->assertDatabaseCount('echeance_occurrences', 3);
    $this->assertDatabaseHas('echeance_occurrences', ['amount' => 12000]);
    $lastOccurrence = EcheanceOccurrence::query()->where('amount', 12000)->firstOrFail();
    expect($lastOccurrence->date->toDateString())->toBe('2026-11-03');
});

test('an infinite échéancier can be created with a single occurrence', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();

    $response = $this->actingAs($user)->post('/echeances', [
        'category_id' => $child->id,
        'description' => 'Abonnement streaming',
        'frequency' => 'mensuelle',
        'occurrences' => [
            ['date' => '2026-09-03', 'amount' => 2000],
        ],
    ]);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('echeances', [
        'user_id' => $user->id,
        'description' => 'Abonnement streaming',
        'occurrences_total' => null,
    ]);
    $this->assertDatabaseCount('echeance_occurrences', 1);
});

test('the number of occurrences must match the declared total', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();

    $response = $this->actingAs($user)->post('/echeances', [
        'category_id' => $child->id,
        'description' => 'Assurance véhicule',
        'frequency' => 'mensuelle',
        'occurrences_total' => 3,
        'occurrences' => [
            ['date' => '2026-09-03', 'amount' => 15000],
        ],
    ]);

    $response->assertSessionHasErrors('occurrences');
    $this->assertDatabaseCount('echeances', 0);
});

test('an échéancier cannot be created under a root category', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();

    $response = $this->actingAs($user)->post('/echeances', [
        'category_id' => $root->id,
        'description' => 'Assurance véhicule',
        'frequency' => 'mensuelle',
        'occurrences' => [['date' => '2026-09-03', 'amount' => 15000]],
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseCount('echeances', 0);
});

test('an échéancier can be cancelled by its owner, cancelling its pending occurrences', function () {
    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $echeance = Echeance::factory()->for($user)->for($child, 'category')->create(['status' => EcheanceStatus::Active]);
    $pending = EcheanceOccurrence::factory()->for($echeance)->create(['status' => EcheanceOccurrenceStatus::Pending]);
    $generated = EcheanceOccurrence::factory()->for($echeance)->generated()->create();

    $response = $this->actingAs($user)->patch("/echeances/{$echeance->id}/cancel");

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('echeances', ['id' => $echeance->id, 'status' => EcheanceStatus::Cancelled->value]);
    $this->assertDatabaseHas('echeance_occurrences', ['id' => $pending->id, 'status' => EcheanceOccurrenceStatus::Cancelled->value]);
    $this->assertDatabaseHas('echeance_occurrences', ['id' => $generated->id, 'status' => EcheanceOccurrenceStatus::Generated->value]);
});

test('an échéancier cannot be cancelled by another user', function () {
    $user = User::factory()->create();
    $echeance = Echeance::factory()->create();

    $response = $this->actingAs($user)->patch("/echeances/{$echeance->id}/cancel");

    $response->assertForbidden();
});
