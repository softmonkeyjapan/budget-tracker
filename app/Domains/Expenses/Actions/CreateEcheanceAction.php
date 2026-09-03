<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Actions;

use App\Domains\Expenses\Actions\Concerns\ResolvesChildCategory;
use App\Domains\Expenses\DataTransferObjects\CreateEcheanceData;
use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Domains\Expenses\Repositories\Contracts\EcheanceRepositoryInterface;
use App\Domains\Shared\Contracts\CategoryLookupInterface;
use App\Models\Echeance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateEcheanceAction
{
    use ResolvesChildCategory;

    public function __construct(
        private readonly EcheanceRepositoryInterface $echeances,
        private readonly CategoryLookupInterface $categories,
    ) {}

    public function execute(User $user, CreateEcheanceData $data): Echeance
    {
        $category = $this->resolveChildCategory($this->categories, $user, $data->categoryId);

        return DB::transaction(function () use ($user, $data, $category) {
            $echeance = $this->echeances->create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'description' => $data->description,
                'frequency' => $data->frequency,
                'default_amount' => $data->occurrences[0]['amount'],
                'occurrences_total' => $data->occurrencesTotal,
                'status' => EcheanceStatus::Active,
            ]);

            $this->echeances->addOccurrences($echeance, array_map(
                fn (array $occurrence): array => [
                    'date' => $occurrence['date'],
                    'amount' => $occurrence['amount'],
                    'status' => EcheanceOccurrenceStatus::Pending,
                ],
                $data->occurrences,
            ));

            return $echeance;
        });
    }
}
