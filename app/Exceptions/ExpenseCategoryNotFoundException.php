<?php

declare(strict_types=1);

namespace App\Exceptions;

final class ExpenseCategoryNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct("La catégorie sélectionnée n'existe pas ou ne vous appartient pas.");
    }
}
