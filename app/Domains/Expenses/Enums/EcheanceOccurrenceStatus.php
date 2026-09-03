<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Enums;

enum EcheanceOccurrenceStatus: string
{
    case Pending = 'en_attente';
    case Generated = 'generee';
    case Cancelled = 'annulee';
}
