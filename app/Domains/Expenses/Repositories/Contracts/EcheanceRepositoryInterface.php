<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Repositories\Contracts;

use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface EcheanceRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Echeance;

    /**
     * @param  array<int, array<string, mixed>>  $occurrences
     */
    public function addOccurrences(Echeance $echeance, array $occurrences): void;

    /**
     * @param  array<string, mixed>  $data
     */
    public function appendOccurrence(Echeance $echeance, array $data): EcheanceOccurrence;

    /**
     * Échéances owned by the user, most recent first, with their occurrences and category loaded.
     *
     * @return Collection<int, Echeance>
     */
    public function forUser(User $user): Collection;

    public function updateStatus(Echeance $echeance, EcheanceStatus $status): Echeance;

    public function cancelPendingOccurrences(Echeance $echeance): void;

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateOccurrence(EcheanceOccurrence $occurrence, array $data): EcheanceOccurrence;

    /**
     * Pending occurrences due on or before the given date, with their échéance loaded.
     *
     * @return Collection<int, EcheanceOccurrence>
     */
    public function duePendingOccurrences(string $date): Collection;

    public function markOccurrenceGenerated(EcheanceOccurrence $occurrence, Expense $expense): EcheanceOccurrence;

    public function incrementGeneratedCount(Echeance $echeance): Echeance;
}
