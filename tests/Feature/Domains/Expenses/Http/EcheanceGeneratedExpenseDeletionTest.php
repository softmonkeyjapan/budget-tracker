<?php

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use App\Models\Expense;
use App\Models\User;

test('deleting an échéance-generated expense cancels its occurrence instead of leaving it stuck', function () {
    $user = User::factory()->create();
    $echeance = Echeance::factory()->for($user)->create(['occurrences_generated' => 1]);
    $expense = Expense::factory()->for($user)->create();
    $occurrence = EcheanceOccurrence::factory()->for($echeance)->generated()->create(['expense_id' => $expense->id]);

    $response = $this->actingAs($user)->delete("/expenses/{$expense->id}");

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);

    $occurrence->refresh();
    expect($occurrence->status)->toBe(EcheanceOccurrenceStatus::Cancelled);
    expect($occurrence->expense_id)->toBeNull();

    $echeance->refresh();
    expect($echeance->occurrences_generated)->toBe(0);
});
