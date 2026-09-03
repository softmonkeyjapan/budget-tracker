<?php

namespace Database\Factories;

use App\Domains\Expenses\Enums\EcheanceFrequency;
use App\Domains\Expenses\Enums\EcheanceStatus;
use App\Models\Category;
use App\Models\Echeance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Echeance>
 */
class EcheanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'description' => fake()->words(3, true),
            'frequency' => EcheanceFrequency::Monthly,
            'default_amount' => fake()->numberBetween(500, 50000),
            'occurrences_total' => null,
            'occurrences_generated' => 0,
            'status' => EcheanceStatus::Active,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => EcheanceStatus::Cancelled]);
    }
}
