<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Resources;

use App\Models\Echeance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Echeance
 */
final class EcheanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'frequency' => $this->frequency->value,
            'default_amount' => $this->default_amount,
            'occurrences_total' => $this->occurrences_total,
            'occurrences_generated' => $this->occurrences_generated,
            'status' => $this->status->value,
            'category' => $this->category !== null ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'root_name' => $this->category->parent?->name,
                'resolved_color' => $this->category->resolvedColor(),
                'resolved_icon' => $this->category->resolvedIcon(),
            ] : null,
            'occurrences' => EcheanceOccurrenceResource::collection($this->whenLoaded('occurrences')),
        ];
    }
}
