<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Expenses\Actions\GenerateDueEcheanceOccurrencesAction;
use Illuminate\Console\Command;

final class GenerateDueEcheanceOccurrences extends Command
{
    protected $signature = 'echeances:generate-due';

    protected $description = 'Génère les dépenses des échéances arrivées à échéance et fait avancer les échéanciers infinis';

    public function handle(GenerateDueEcheanceOccurrencesAction $action): int
    {
        $count = $action->execute();

        $this->info("{$count} échéance(s) générée(s).");

        return self::SUCCESS;
    }
}
