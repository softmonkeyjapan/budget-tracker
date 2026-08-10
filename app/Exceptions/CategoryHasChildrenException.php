<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Support\Exceptions\DomainException;

final class CategoryHasChildrenException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Impossible de supprimer une catégorie qui a des enfants.');
    }
}
