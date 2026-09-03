<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Repositories;

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class EloquentEcheanceRepository implements EcheanceRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Echeance
    {
        return Echeance::create($data);
    }

    /**
     * @param  array<int, array<string, mixed>>  $occurrences
     */
    public function addOccurrences(Echeance $echeance, array $occurrences): void
    {
        $echeance->occurrences()->createMany($occurrences);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function appendOccurrence(Echeance $echeance, array $data): EcheanceOccurrence
    {
        return $echeance->occurrences()->create($data);
    }

    /**
     * @return Collection<int, Echeance>
     */
    public function forUser(User $user): Collection
    {
        return Echeance::query()
            ->where('user_id', $user->id)
            ->with(['category.parent', 'occurrences' => fn ($query) => $query->orderBy('date')])
            ->orderByDesc('created_at')
            ->get();
    }

    public function updateStatus(Echeance $echeance, EcheanceStatus $status): Echeance
    {
        $echeance->update(['status' => $status]);

        return $echeance;
    }

    public function cancelPendingOccurrences(Echeance $echeance): void
    {
        $echeance->occurrences()
            ->where('status', EcheanceOccurrenceStatus::Pending)
            ->update(['status' => EcheanceOccurrenceStatus::Cancelled]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateOccurrence(EcheanceOccurrence $occurrence, array $data): EcheanceOccurrence
    {
        $occurrence->fill($data);
        $occurrence->save();

        return $occurrence;
    }

    /**
     * @return Collection<int, EcheanceOccurrence>
     */
    public function duePendingOccurrences(string $date): Collection
    {
        return EcheanceOccurrence::query()
            ->where('status', EcheanceOccurrenceStatus::Pending)
            ->whereDate('date', '<=', $date)
            ->with('echeance')
            ->get();
    }

    public function markOccurrenceGenerated(EcheanceOccurrence $occurrence, Expense $expense): EcheanceOccurrence
    {
        $occurrence->update([
            'status' => EcheanceOccurrenceStatus::Generated,
            'expense_id' => $expense->id,
        ]);

        return $occurrence;
    }

    public function incrementGeneratedCount(Echeance $echeance): Echeance
    {
        $echeance->increment('occurrences_generated');

        return $echeance;
    }
}
