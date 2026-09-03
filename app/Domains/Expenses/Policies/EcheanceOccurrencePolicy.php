<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Policies;

use App\Models\EcheanceOccurrence;
use App\Models\User;

final class EcheanceOccurrencePolicy
{
    public function update(User $user, EcheanceOccurrence $occurrence): bool
    {
        return $user->id === $occurrence->echeance->user_id;
    }
}
