<?php

namespace Database\Factories;

use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->numberBetween(500, 50000),
            'date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'description' => null,
            'status' => ExpenseStatus::Validated,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'category_id' => null,
            'status' => ExpenseStatus::Draft,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'category_id' => null,
            'amount' => null,
            'status' => ExpenseStatus::Rejected,
            'raw_payload' => "Notification brute non reconnue\nligne 2",
        ]);
    }
}
