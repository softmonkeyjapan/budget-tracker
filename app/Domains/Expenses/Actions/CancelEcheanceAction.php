<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Domains\Expenses\Exceptions\EcheanceAlreadyCancelledException;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Models\Echeance;
use Illuminate\Support\Facades\DB;

final class CancelEcheanceAction
{
    public function __construct(
        private readonly EcheanceRepositoryInterface $echeances,
    ) {}

    public function execute(Echeance $echeance): Echeance
    {
        if ($echeance->status === EcheanceStatus::Cancelled) {
            throw new EcheanceAlreadyCancelledException;
        }

        return DB::transaction(function () use ($echeance) {
            $this->echeances->cancelPendingOccurrences($echeance);

            return $this->echeances->updateStatus($echeance, EcheanceStatus::Cancelled);
        });
    }
}
