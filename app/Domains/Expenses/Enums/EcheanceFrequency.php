<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Enums;

enum EcheanceFrequency: string
{
    case Monthly = 'mensuelle';
    case Quarterly = 'trimestrielle';
    case Yearly = 'annuelle';

    public function months(): int
    {
        return match ($this) {
            self::Monthly => 1,
            self::Quarterly => 3,
            self::Yearly => 12,
        };
    }
}
