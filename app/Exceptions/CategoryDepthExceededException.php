<?php

declare(strict_types=1);

namespace App\Exceptions;

final class CategoryDepthExceededException extends DomainException
{
    public function __construct()
    {
        parent::__construct("Impossible d'ajouter un enfant à une catégorie qui est elle-même un enfant.");
    }
}
