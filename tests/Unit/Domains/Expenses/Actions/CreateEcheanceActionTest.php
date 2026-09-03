<?php

use App\Domains\Expenses\Actions\CreateEcheanceAction;
use App\Domains\Expenses\DataTransferObjects\CreateEcheanceData;
use App\Domains\Expenses\Enums\EcheanceFrequency;
use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Domains\Expenses\Exceptions\ExpenseCategoryMustBeChildException;
use App\Domains\Expenses\Exceptions\ExpenseCategoryNotFoundException;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Category;
use App\Models\Echeance;
use App\Models\User;

test('creating an échéancier under a child category succeeds', function () {
    $user = User::factory()->make(['id' => 1]);
    $child = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => 1]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->with($user, 5)->andReturn($child);

    $echeance = new Echeance(['id' => 10]);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('create')
        ->once()
        ->with([
            'user_id' => 1,
            'category_id' => 5,
            'description' => 'Assurance véhicule',
            'frequency' => EcheanceFrequency::Monthly,
            'default_amount' => 15000,
            'occurrences_total' => 3,
            'status' => EcheanceStatus::Active,
        ])
        ->andReturn($echeance);

    $echeances->shouldReceive('addOccurrences')
        ->once()
        ->with($echeance, [
            ['date' => '2026-09-03', 'amount' => 15000, 'status' => EcheanceOccurrenceStatus::Pending],
            ['date' => '2026-10-03', 'amount' => 15000, 'status' => EcheanceOccurrenceStatus::Pending],
            ['date' => '2026-11-03', 'amount' => 12000, 'status' => EcheanceOccurrenceStatus::Pending],
        ]);

    $action = new CreateEcheanceAction($echeances, $categories);

    $result = $action->execute($user, new CreateEcheanceData(
        categoryId: 5,
        description: 'Assurance véhicule',
        frequency: EcheanceFrequency::Monthly,
        occurrencesTotal: 3,
        occurrences: [
            ['date' => '2026-09-03', 'amount' => 15000],
            ['date' => '2026-10-03', 'amount' => 15000],
            ['date' => '2026-11-03', 'amount' => 12000],
        ],
    ));

    expect($result)->toBe($echeance);
});

test('creating an échéancier under a root category throws', function () {
    $user = User::factory()->make(['id' => 1]);
    $root = Category::factory()->make(['id' => 5, 'user_id' => 1, 'parent_id' => null]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn($root);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('create')->never();

    $action = new CreateEcheanceAction($echeances, $categories);

    expect(fn () => $action->execute($user, new CreateEcheanceData(
        categoryId: 5,
        description: 'Assurance véhicule',
        frequency: EcheanceFrequency::Monthly,
        occurrencesTotal: 1,
        occurrences: [['date' => '2026-09-03', 'amount' => 15000]],
    )))->toThrow(ExpenseCategoryMustBeChildException::class);
});

test('creating an échéancier under a category that cannot be found throws', function () {
    $user = User::factory()->make(['id' => 1]);

    $categories = Mockery::mock(CategoryLookupInterface::class);
    $categories->shouldReceive('findOwnedByUser')->once()->andReturn(null);

    $echeances = Mockery::mock(EcheanceRepositoryInterface::class);
    $echeances->shouldReceive('create')->never();

    $action = new CreateEcheanceAction($echeances, $categories);

    expect(fn () => $action->execute($user, new CreateEcheanceData(
        categoryId: 99,
        description: 'Assurance véhicule',
        frequency: EcheanceFrequency::Monthly,
        occurrencesTotal: 1,
        occurrences: [['date' => '2026-09-03', 'amount' => 15000]],
    )))->toThrow(ExpenseCategoryNotFoundException::class);
});
