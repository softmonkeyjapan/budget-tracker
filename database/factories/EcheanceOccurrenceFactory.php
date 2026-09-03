<?php

namespace Database\Factories;

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Models\Echeance;
use App\Models\EcheanceOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EcheanceOccurrence>
 */
class EcheanceOccurrenceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'echeance_id' => Echeance::factory(),
            'expense_id' => null,
            'date' => fake()->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
            'amount' => fake()->numberBetween(500, 50000),
            'status' => EcheanceOccurrenceStatus::Pending,
        ];
    }

    public function generated(): static
    {
        return $this->state(fn () => ['status' => EcheanceOccurrenceStatus::Generated]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => EcheanceOccurrenceStatus::Cancelled]);
    }
}
