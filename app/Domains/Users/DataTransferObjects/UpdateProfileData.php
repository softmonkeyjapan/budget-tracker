<?php

declare(strict_types=1);

namespace App\Domains\Users\DataTransferObjects;

use App\Domains\Users\Http\Requests\ProfileUpdateRequest;

final readonly class UpdateProfileData
{
    public function __construct(
        public string $name,
        public string $email,
    ) {}

    public static function fromRequest(ProfileUpdateRequest $request): self
    {
        return new self(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
        );
    }
}
