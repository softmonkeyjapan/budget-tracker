<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DataTransferObjects;

use App\Domains\Expenses\Enums\EcheanceFrequency;
use App\Domains\Expenses\Http\Requests\StoreEcheanceRequest;

final readonly class CreateEcheanceData
{
    /**
     * @param  array<int, array{date: string, amount: int}>  $occurrences
     */
    public function __construct(
        public int $categoryId,
        public string $description,
        public EcheanceFrequency $frequency,
        public ?int $occurrencesTotal,
        public array $occurrences,
    ) {}

    public static function fromRequest(StoreEcheanceRequest $request): self
    {
        return new self(
            categoryId: $request->integer('category_id'),
            description: $request->string('description')->toString(),
            frequency: EcheanceFrequency::from($request->string('frequency')->toString()),
            occurrencesTotal: $request->integer('occurrences_total') ?: null,
            occurrences: array_map(
                fn (array $occurrence): array => [
                    'date' => (string) $occurrence['date'],
                    'amount' => (int) $occurrence['amount'],
                ],
                $request->array('occurrences'),
            ),
        );
    }
}
