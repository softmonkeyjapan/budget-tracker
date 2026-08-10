<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Http\Resources;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Income
 */
final class IncomeResource extends JsonResource
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
        ];
    }
}
