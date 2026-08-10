<?php

declare(strict_types=1);

namespace App\Domains\Categories\DataTransferObjects;

use App\Domains\Categories\Http\Requests\StoreCategoryRequest;

final readonly class CreateCategoryData
{
    public function __construct(
        public string $name,
        public ?string $color,
        public ?string $icon,
        public ?int $parentId,
    ) {}

    public static function fromRequest(StoreCategoryRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            color: $request->string('color')->toString() ?: null,
            icon: $request->string('icon')->toString() ?: null,
            parentId: $request->integer('parent_id') ?: null,
        );
    }
}
