<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Policies;

use App\Models\Echeance;
use App\Models\User;

final class EcheancePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Echeance $echeance): bool
    {
        return $user->id === $echeance->user_id;
    }
}
