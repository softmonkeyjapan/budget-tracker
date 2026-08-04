<?php

declare(strict_types=1);

namespace App\Exceptions;

final class GithubIssueCreationFailedException extends DomainException
{
    public function __construct()
    {
        parent::__construct("L'envoi de votre retour a échoué. Réessayez plus tard.");
    }
}
