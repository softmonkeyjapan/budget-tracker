<?php

use App\Domains\Expenses\Actions\CancelEcheanceAction;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Domains\Expenses\Exceptions\EcheanceAlreadyCancelledException;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Models\Echeance;

test('cancelling an active échéancier cancels its pending occurrences and marks it cancelled', function () {
    $echeance = Echeance::factory()->make(['id' => 7, 'user_id' => 1, 'category_id' => 1, 'status' => EcheanceStatus::Active]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('cancelPendingOccurrences')->once()->with($echeance);
    $echeances->shouldReceive('updateStatus')->once()->with($echeance, EcheanceStatus::Cancelled)->andReturn($echeance);

    $action = new CancelEcheanceAction($echeances);

    expect($action->execute($echeance))->toBe($echeance);
});

test('cancelling an already cancelled échéancier throws', function () {
    $echeance = Echeance::factory()->make(['id' => 7, 'user_id' => 1, 'category_id' => 1, 'status' => EcheanceStatus::Cancelled]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('cancelPendingOccurrences')->never();
    $echeances->shouldReceive('updateStatus')->never();

    $action = new CancelEcheanceAction($echeances);

    expect(fn () => $action->execute($echeance))->toThrow(EcheanceAlreadyCancelledException::class);
});
