<?php

use App\Domains\Expenses\Actions\UpdateEcheanceOccurrenceAction;
use App\Domains\Expenses\DataTransferObjects\UpdateEcheanceOccurrenceData;
use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Exceptions\EcheanceOccurrenceNotEditableException;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Models\EcheanceOccurrence;

test('editing a pending occurrence updates its date and amount', function () {
    $occurrence = EcheanceOccurrence::factory()->make(['id' => 3, 'echeance_id' => 1, 'status' => EcheanceOccurrenceStatus::Pending]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('updateOccurrence')
        ->once()
        ->with($occurrence, ['date' => '2026-12-03', 'amount' => 9000])
        ->andReturn($occurrence);

    $action = new UpdateEcheanceOccurrenceAction($echeances);

    expect($action->execute($occurrence, new UpdateEcheanceOccurrenceData(date: '2026-12-03', amount: 9000)))
        ->toBe($occurrence);
});

test('editing an already generated occurrence throws', function () {
    $occurrence = EcheanceOccurrence::factory()->make(['id' => 3, 'echeance_id' => 1, 'status' => EcheanceOccurrenceStatus::Generated]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('updateOccurrence')->never();

    $action = new UpdateEcheanceOccurrenceAction($echeances);

    expect(fn () => $action->execute($occurrence, new UpdateEcheanceOccurrenceData(date: '2026-12-03', amount: 9000)))
        ->toThrow(EcheanceOccurrenceNotEditableException::class);
});

test('editing a cancelled occurrence throws', function () {
    $occurrence = EcheanceOccurrence::factory()->make(['id' => 3, 'echeance_id' => 1, 'status' => EcheanceOccurrenceStatus::Cancelled]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('updateOccurrence')->never();

    $action = new UpdateEcheanceOccurrenceAction($echeances);

    expect(fn () => $action->execute($occurrence, new UpdateEcheanceOccurrenceData(date: '2026-12-03', amount: 9000)))
        ->toThrow(EcheanceOccurrenceNotEditableException::class);
});
