<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DataTransferObjects;

use App\Domains\Expenses\Http\Requests\UpdateEcheanceOccurrenceRequest;

final readonly class UpdateEcheanceOccurrenceData
{
    public function __construct(
        public string $date,
        public int $amount,
    ) {}

    public static function fromRequest(UpdateEcheanceOccurrenceRequest $request): self
    {
        return new self(
            date: $request->string('date')->toString(),
            amount: $request->integer('amount'),
        );
    }
}
