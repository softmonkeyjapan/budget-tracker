<?php

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use App\Models\User;

test('a pending occurrence can be edited by the échéancier owner', function () {
    $user = User::factory()->create();
    $echeance = Echeance::factory()->for($user)->create();
    $occurrence = EcheanceOccurrence::factory()->for($echeance)->create([
        'status' => EcheanceOccurrenceStatus::Pending,
        'date' => '2026-09-03',
        'amount' => 15000,
    ]);

    $response = $this->actingAs($user)->patch("/echeances/occurrences/{$occurrence->id}", [
        'date' => '2026-09-05',
        'amount' => 16000,
    ]);

    $response->assertSessionHasNoErrors();
    $occurrence->refresh();
    expect($occurrence->date->toDateString())->toBe('2026-09-05');
    expect($occurrence->amount)->toBe(16000);
});

test('a generated occurrence cannot be edited', function () {
    $user = User::factory()->create();
    $echeance = Echeance::factory()->for($user)->create();
    $occurrence = EcheanceOccurrence::factory()->for($echeance)->generated()->create(['amount' => 15000]);

    $response = $this->actingAs($user)->patch("/echeances/occurrences/{$occurrence->id}", [
        'date' => '2026-09-05',
        'amount' => 16000,
    ]);

    $response->assertSessionHasErrors('message');
    $this->assertDatabaseHas('echeance_occurrences', ['id' => $occurrence->id, 'amount' => 15000]);
});

test('an occurrence cannot be edited by another user', function () {
    $user = User::factory()->create();
    $occurrence = EcheanceOccurrence::factory()->create();

    $response = $this->actingAs($user)->patch("/echeances/occurrences/{$occurrence->id}", [
        'date' => '2026-09-05',
        'amount' => 16000,
    ]);

    $response->assertForbidden();
});
