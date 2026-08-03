<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

final class SeedE2eUsers extends Command
{
    protected $signature = 'e2e:seed';

    protected $description = 'Seed the users and sample data required by the Playwright e2e test suite';

    public function handle(): int
    {
        $verified = User::query()->updateOrCreate(
            ['email' => 'e2e-verified@example.com'],
            [
                'name' => 'E2E Verified',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'e2e-unverified@example.com'],
            [
                'name' => 'E2E Unverified',
                'password' => Hash::make('password'),
                'email_verified_at' => null,
            ],
        );

        $this->seedCategories($verified);
        $this->seedExpenses($verified);
        $this->seedIncomes($verified);

        return self::SUCCESS;
    }

    /**
     * Mirrors the sample category tree from docs/design/build/index.html#categories.
     */
    private function seedCategories(User $user): void
    {
        $roots = [
            ['name' => 'Alimentaire', 'icon' => '▾', 'color' => '#23C48E', 'children' => ['Alimentation générale', 'Boucherie', 'Restaurants']],
            ['name' => 'Charges fixes', 'icon' => '⌂', 'color' => '#FF8A66', 'children' => ['Loyer', 'Internet', 'Électricité']],
            ['name' => 'Transport', 'icon' => '▣', 'color' => '#2F80ED', 'children' => ['Carburant', 'Entretien', 'Bus']],
            ['name' => 'Loisirs', 'icon' => '✦', 'color' => '#8A5CF6', 'children' => ['Sorties', 'Jeux', 'Sport']],
            ['name' => 'Santé', 'icon' => '♥', 'color' => '#FF5B62', 'children' => ['Pharmacie', 'Médecin']],
        ];

        foreach ($roots as $data) {
            $root = Category::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $data['name'], 'parent_id' => null],
                ['icon' => $data['icon'], 'color' => $data['color']],
            );

            foreach ($data['children'] as $childName) {
                Category::query()->updateOrCreate(
                    ['user_id' => $user->id, 'name' => $childName, 'parent_id' => $root->id],
                );
            }
        }
    }

    /**
     * Mirrors the sample "5 dernières dépenses" from docs/design/build/index.html#dashboard.
     */
    private function seedExpenses(User $user): void
    {
        $recent = [
            ['Charges fixes', 'Loyer', '2026-08-01', 'Appartement', 82000],
            ['Alimentaire', 'Alimentation générale', '2026-08-03', 'Supermarché', 14850],
            ['Transport', 'Carburant', '2026-08-08', 'Station-service', 8200],
            ['Loisirs', 'Sorties', '2026-08-12', 'Cinéma', 3400],
            ['Santé', 'Pharmacie', '2026-08-15', 'Ordonnance', 5250],
        ];

        foreach ($recent as [$rootName, $childName, $date, $description, $amount]) {
            $child = Category::query()
                ->whereHas('parent', fn ($query) => $query->where('user_id', $user->id)->where('name', $rootName))
                ->where('name', $childName)
                ->first();

            if ($child === null) {
                continue;
            }

            Expense::query()->updateOrCreate(
                ['user_id' => $user->id, 'category_id' => $child->id, 'date' => $date],
                ['amount' => $amount, 'description' => $description],
            );
        }
    }

    /**
     * Mirrors the sample income entries from docs/design/build/index.html#dashboard.
     */
    private function seedIncomes(User $user): void
    {
        $recent = [
            ['2026-08-03', 'Salaire août 2026', 230000],
            ['2026-08-21', 'Mission freelance', 30000],
        ];

        foreach ($recent as [$date, $description, $amount]) {
            Income::query()->updateOrCreate(
                ['user_id' => $user->id, 'date' => $date, 'description' => $description],
                ['amount' => $amount],
            );
        }
    }
}
