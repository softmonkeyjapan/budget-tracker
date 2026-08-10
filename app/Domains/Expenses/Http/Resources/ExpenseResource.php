<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Resources;

use App\Models\Expense;
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
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'date' => $this->date->toDateString(),
            'description' => $this->description,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'root_name' => $this->category->parent?->name,
                'resolved_color' => $this->category->resolvedColor(),
                'resolved_icon' => $this->category->resolvedIcon(),
            ],
        ];
    }
}
