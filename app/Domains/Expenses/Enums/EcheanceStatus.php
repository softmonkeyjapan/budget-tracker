<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Enums;

enum EcheanceStatus: string
{
    case Active = 'active';
    case Cancelled = 'annulee';
}
