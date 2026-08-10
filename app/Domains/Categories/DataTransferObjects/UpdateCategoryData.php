<?php

declare(strict_types=1);

namespace App\Domains\Categories\DataTransferObjects;

use App\Domains\Categories\Http\Requests\UpdateCategoryRequest;

final readonly class UpdateCategoryData
{
    public function __construct(
        public string $name,
        public ?string $color,
        public ?string $icon,
    ) {}

    public static function fromRequest(UpdateCategoryRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            color: $request->string('color')->toString() ?: null,
            icon: $request->string('icon')->toString() ?: null,
        );
    }
}
