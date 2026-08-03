<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Expense;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Expense
 */
final class ExpenseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $categories = app(CategoryService::class);

        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'date' => $this->date->toDateString(),
            'description' => $this->description,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'root_name' => $this->category->parent?->name,
                'resolved_color' => $categories->resolveColor($this->category),
                'resolved_icon' => $categories->resolveIcon($this->category),
            ],
        ];
    }
}
