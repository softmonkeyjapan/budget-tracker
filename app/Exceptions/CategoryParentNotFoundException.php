<?php

declare(strict_types=1);

namespace App\Exceptions;

final class CategoryParentNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct("La catégorie racine sélectionnée n'existe pas ou ne vous appartient pas.");
    }
}
