<?php

declare(strict_types=1);

namespace App\Domains\Incomes\DataTransferObjects;

use App\Domains\Incomes\Http\Requests\UpdateIncomeRequest;

final readonly class UpdateIncomeData
{
    public function __construct(
        public int $amount,
        public string $date,
        public ?string $description,
    ) {}

    public static function fromRequest(UpdateIncomeRequest $request): self
    {
        return new self(
            amount: $request->integer('amount'),
            date: $request->string('date')->toString(),
            description: $request->string('description')->toString() ?: null,
        );
    }
}
