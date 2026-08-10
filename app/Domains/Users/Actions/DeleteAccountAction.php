<?php

declare(strict_types=1);

namespace App\Domains\Users\Actions;

use App\Domains\Users\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;

final class DeleteAccountAction
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function execute(User $user): void
    {
        $this->users->delete($user);
    }
}
