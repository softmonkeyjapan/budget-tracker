<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'parent_id' => null,
            'name' => fake()->unique()->word(),
            'color' => null,
            'icon' => null,
        ];
    }

    public function child(Category $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $parent->user_id,
            'parent_id' => $parent->id,
        ]);
    }
}
