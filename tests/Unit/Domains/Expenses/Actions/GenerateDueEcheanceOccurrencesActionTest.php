<?php

use App\Domains\Expenses\Actions\GenerateDueEcheanceOccurrencesAction;
use App\Domains\Expenses\Enums\EcheanceFrequency;
use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use App\Models\Expense;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;

afterEach(function () {
    Carbon::setTestNow();
});

test('generating a due occurrence of a finite échéancier creates the expense and does not append a new occurrence', function () {
    Carbon::setTestNow('2026-10-03');

    $echeance = Echeance::factory()->make([
        'id' => 1,
        'user_id' => 42,
        'category_id' => 9,
        'description' => 'Assurance véhicule',
        'frequency' => EcheanceFrequency::Monthly,
        'default_amount' => 15000,
        'occurrences_total' => 3,
        'status' => EcheanceStatus::Active,
    ]);

    $occurrence = EcheanceOccurrence::factory()->make([
        'id' => 5,
        'echeance_id' => 1,
        'date' => '2026-10-03',
        'amount' => 15000,
        'status' => EcheanceOccurrenceStatus::Pending,
    ]);
    $occurrence->setRelation('echeance', $echeance);

    $expense = new Expense(['id' => 100]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('duePendingOccurrences')->once()->with('2026-10-03')->andReturn(new EloquentCollection([$occurrence]));
    $echeances->shouldReceive('markOccurrenceGenerated')->once()->with($occurrence, $expense);
    $echeances->shouldReceive('incrementGeneratedCount')->once()->with($echeance);
    $echeances->shouldReceive('appendOccurrence')->never();

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 42,
            'category_id' => 9,
            'amount' => 15000,
            'date' => '2026-10-03',
            'description' => 'Assurance véhicule',
            'status' => ExpenseStatus::Validated,
        ])
        ->andReturn($expense);

    $action = new GenerateDueEcheanceOccurrencesAction($echeances, $expenses);

    expect($action->execute())->toBe(1);
});

test('generating a due occurrence of an infinite échéancier appends the next occurrence', function () {
    Carbon::setTestNow('2026-10-31');

    $echeance = Echeance::factory()->make([
        'id' => 2,
        'user_id' => 42,
        'category_id' => 9,
        'description' => 'Abonnement',
        'frequency' => EcheanceFrequency::Monthly,
        'default_amount' => 2000,
        'occurrences_total' => null,
        'status' => EcheanceStatus::Active,
    ]);

    $occurrence = EcheanceOccurrence::factory()->make([
        'id' => 6,
        'echeance_id' => 2,
        'date' => '2026-10-31',
        'amount' => 2000,
        'status' => EcheanceOccurrenceStatus::Pending,
    ]);
    $occurrence->setRelation('echeance', $echeance);

    $expense = new Expense(['id' => 101]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('duePendingOccurrences')->once()->with('2026-10-31')->andReturn(new EloquentCollection([$occurrence]));
    $echeances->shouldReceive('markOccurrenceGenerated')->once();
    $echeances->shouldReceive('incrementGeneratedCount')->once();
    $echeances->shouldReceive('appendOccurrence')
        ->once()
        ->with($echeance, [
            'date' => '2026-11-30',
            'amount' => 2000,
            'status' => EcheanceOccurrenceStatus::Pending,
        ]);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('create')->once()->andReturn($expense);

    $action = new GenerateDueEcheanceOccurrencesAction($echeances, $expenses);

    expect($action->execute())->toBe(1);
});

test('returns 0 when nothing is due', function () {
    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('duePendingOccurrences')->once()->andReturn(new EloquentCollection);

    $expenses = Mockery::mock(ExpenseRepositoryInterface::class);
    $expenses->shouldReceive('create')->never();

    $action = new GenerateDueEcheanceOccurrencesAction($echeances, $expenses);

    expect($action->execute())->toBe(0);
});
