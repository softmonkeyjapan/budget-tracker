<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Exceptions;

use App\Support\Exceptions\DomainException;

final class ExpenseAlreadyValidatedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cette dépense est déjà validée, elle ne peut pas être rejetée.');
    }
}
