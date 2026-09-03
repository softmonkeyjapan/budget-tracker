<?php

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use App\Models\User;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('the command generates an expense for each due pending occurrence', function () {
    Carbon::setTestNow('2026-10-03');

    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $echeance = Echeance::factory()->for($user)->for($child, 'category')->create([
        'description' => 'Assurance véhicule',
        'occurrences_total' => 3,
    ]);
    $due = EcheanceOccurrence::factory()->for($echeance)->create(['date' => '2026-10-03', 'amount' => 15000]);
    $future = EcheanceOccurrence::factory()->for($echeance)->create(['date' => '2026-11-03', 'amount' => 12000]);

    $this->artisan('echeances:generate-due')->assertSuccessful();

    $this->assertDatabaseHas('expenses', [
        'user_id' => $user->id,
        'category_id' => $child->id,
        'amount' => 15000,
        'description' => 'Assurance véhicule',
        'status' => ExpenseStatus::Validated->value,
    ]);
    $due->refresh();
    expect($due->status)->toBe(EcheanceOccurrenceStatus::Generated);
    expect($due->expense_id)->not->toBeNull();

    $future->refresh();
    expect($future->status)->toBe(EcheanceOccurrenceStatus::Pending);

    $echeance->refresh();
    expect($echeance->occurrences_generated)->toBe(1);
});

test('the command extends an infinite échéancier by appending its next occurrence', function () {
    Carbon::setTestNow('2026-10-31');

    $user = User::factory()->create();
    $root = Category::factory()->for($user)->create();
    $child = Category::factory()->child($root)->create();
    $echeance = Echeance::factory()->for($user)->for($child, 'category')->create([
        'occurrences_total' => null,
        'default_amount' => 2000,
    ]);
    EcheanceOccurrence::factory()->for($echeance)->create(['date' => '2026-10-31', 'amount' => 2000]);

    $this->artisan('echeances:generate-due')->assertSuccessful();

    $next = EcheanceOccurrence::query()->where('echeance_id', $echeance->id)->where('status', EcheanceOccurrenceStatus::Pending)->first();

    expect($next)->not->toBeNull();
    expect($next->date->toDateString())->toBe('2026-11-30');
    expect($next->amount)->toBe(2000);
});
