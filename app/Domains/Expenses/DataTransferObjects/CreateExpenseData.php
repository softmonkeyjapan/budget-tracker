<?php

declare(strict_types=1);

namespace App\Domains\Expenses\DataTransferObjects;

use App\Domains\Expenses\Http\Requests\StoreExpenseRequest;

final readonly class CreateExpenseData
{
    public function __construct(
        public int $categoryId,
        public int $amount,
        public string $date,
        public ?string $description,
    ) {}

    public static function fromRequest(StoreExpenseRequest $request): self
    {
        return new self(
            categoryId: $request->integer('category_id'),
            amount: $request->integer('amount'),
            date: $request->string('date')->toString(),
            description: $request->string('description')->toString() ?: null,
        );
    }
}
