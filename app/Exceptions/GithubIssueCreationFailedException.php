<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\Exceptions\DomainException;

final class GithubIssueCreationFailedException extends DomainException
{
    public function __construct()
    {
        parent::__construct("L'envoi de votre retour a échoué. Réessayez plus tard.");
    }
}
