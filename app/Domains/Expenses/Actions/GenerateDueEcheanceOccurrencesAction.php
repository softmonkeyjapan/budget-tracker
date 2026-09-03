<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Domains\Expenses\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Models\EcheanceOccurrence;
use Illuminate\Support\Facades\DB;

final class GenerateDueEcheanceOccurrencesAction
{
    public function __construct(
        private readonly EcheanceRepositoryInterface $echeances,
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function execute(): int
    {
        $due = $this->echeances->duePendingOccurrences(now()->toDateString());

        foreach ($due as $occurrence) {
            $this->generate($occurrence);
        }

        return $due->count();
    }

    private function generate(EcheanceOccurrence $occurrence): void
    {
        DB::transaction(function () use ($occurrence) {
            $echeance = $occurrence->echeance;

            $expense = $this->expenses->create([
                'user_id' => $echeance->user_id,
                'category_id' => $echeance->category_id,
                'amount' => $occurrence->amount,
                'date' => $occurrence->date->toDateString(),
                'description' => $echeance->description,
                'status' => ExpenseStatus::Validated,
            ]);

            $this->echeances->markOccurrenceGenerated($occurrence, $expense);
            $this->echeances->incrementGeneratedCount($echeance);

            if ($echeance->occurrences_total === null && $echeance->status === EcheanceStatus::Active) {
                $this->echeances->appendOccurrence($echeance, [
                    'date' => $occurrence->date->copy()->addMonthsNoOverflow($echeance->frequency->months())->toDateString(),
                    'amount' => $echeance->default_amount,
                    'status' => EcheanceOccurrenceStatus::Pending,
                ]);
            }
        });
    }
}
