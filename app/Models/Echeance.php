<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Expenses\Enums\EcheanceFrequency;
use App\Domains\Expenses\Enums\EcheanceStatus;
use Database\Factories\EcheanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'category_id', 'description', 'frequency', 'default_amount', 'occurrences_total', 'occurrences_generated', 'status'])]
class Echeance extends Model
{
    /** @use HasFactory<EcheanceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'default_amount' => 'integer',
            'occurrences_total' => 'integer',
            'occurrences_generated' => 'integer',
            'frequency' => EcheanceFrequency::class,
            'status' => EcheanceStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<EcheanceOccurrence, $this>
     */
    public function occurrences(): HasMany
    {
        return $this->hasMany(EcheanceOccurrence::class);
    }
}
