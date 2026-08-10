<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\Exceptions\DomainException;

final class ExpenseCategoryMustBeChildException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Une dépense doit être rattachée à une catégorie enfant, pas à une catégorie racine.');
    }
}
