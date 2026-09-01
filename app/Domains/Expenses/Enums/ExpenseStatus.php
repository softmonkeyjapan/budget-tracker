<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Enums;

enum ExpenseStatus: string
{
    case Draft = 'brouillon';
    case Validated = 'validee';
    case Rejected = 'rejetee';
}
