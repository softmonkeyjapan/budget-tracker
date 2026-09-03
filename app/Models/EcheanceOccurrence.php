<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use Database\Factories\EcheanceOccurrenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['echeance_id', 'expense_id', 'date', 'amount', 'status'])]
class EcheanceOccurrence extends Model
{
    /** @use HasFactory<EcheanceOccurrenceFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'integer',
            'status' => EcheanceOccurrenceStatus::class,
        ];
    }

    public function echeance(): BelongsTo
    {
        return $this->belongsTo(Echeance::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }
}
