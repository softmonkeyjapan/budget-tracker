<?php

declare(strict_types=1);

namespace App\Domains\Users\Actions;

use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class UpdatePasswordAction
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(User $user, string $newPassword): void
    {
        $this->users->update($user, [
            'password' => Hash::make($newPassword),
        ]);
    }
}
