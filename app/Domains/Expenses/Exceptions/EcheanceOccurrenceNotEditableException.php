<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Exceptions;

use App\Support\Exceptions\DomainException;

final class EcheanceOccurrenceNotEditableException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cette échéance a déjà été générée ou annulée, elle ne peut plus être modifiée.');
    }
}
