<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Resources;

use App\Models\EcheanceOccurrence;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin EcheanceOccurrence
 */
final class EcheanceOccurrenceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->toDateString(),
            'amount' => $this->amount,
            'status' => $this->status->value,
        ];
    }
}
