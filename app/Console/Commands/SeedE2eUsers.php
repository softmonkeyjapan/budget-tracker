<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

final class SeedE2eUsers extends Command
{
    protected $signature = 'e2e:seed';

    protected $description = 'Seed the users required by the Playwright e2e test suite';

    public function handle(): int
    {
        User::query()->updateOrCreate(
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

        return self::SUCCESS;
    }
}
