<?php

declare(strict_types=1);

namespace App\Domains\Categories\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
final class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
            'parent_id' => $this->parent_id,
            'resolved_color' => $this->resolvedColor(),
            'resolved_icon' => $this->resolvedIcon(),
            'color_inherited' => $this->parent_id !== null && $this->color === null,
            'icon_inherited' => $this->parent_id !== null && $this->icon === null,
            'children_count' => $this->when(
                $this->relationLoaded('children'),
                fn () => $this->children->count(),
            ),
            'children' => CategoryResource::collection($this->whenLoaded('children')),
        ];
    }
}
