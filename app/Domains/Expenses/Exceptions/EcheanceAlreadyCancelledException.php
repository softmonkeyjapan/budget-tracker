<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Exceptions;

use App\Support\Exceptions\DomainException;

final class EcheanceAlreadyCancelledException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cet échéancier est déjà annulé.');
    }
}
