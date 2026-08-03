<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
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
}
