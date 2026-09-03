<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\DataTransferObjects\UpdateEcheanceOccurrenceData;
use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Exceptions\EcheanceOccurrenceNotEditableException;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Models\EcheanceOccurrence;

final class UpdateEcheanceOccurrenceAction
{
    public function __construct(
        private readonly EcheanceRepositoryInterface $echeances,
    ) {}

    public function execute(EcheanceOccurrence $occurrence, UpdateEcheanceOccurrenceData $data): EcheanceOccurrence
    {
        if ($occurrence->status !== EcheanceOccurrenceStatus::Pending) {
            throw new EcheanceOccurrenceNotEditableException;
        }

        return $this->echeances->updateOccurrence($occurrence, [
            'date' => $data->date,
            'amount' => $data->amount,
        ]);
    }
}
