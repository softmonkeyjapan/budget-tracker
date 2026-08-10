<?php

declare(strict_types=1);

namespace App\Domains\Users\Actions;

use App\Domains\Users\DataTransferObjects\UpdateProfileData;
use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;

final class UpdateProfileAction
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(User $user, UpdateProfileData $data): User
    {
        $attributes = [
            'name' => $data->name,
            'email' => $data->email,
        ];

        if ($user->email !== $data->email) {
            $attributes['email_verified_at'] = null;
        }

        return $this->users->update($user, $attributes);
    }
}
