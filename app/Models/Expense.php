<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Expenses\Enums\EcheanceOccurrenceStatus;
use App\Domains\Expenses\Enums\ExpenseStatus;
use App\Support\SearchNormalizer;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'category_id', 'amount', 'date', 'description', 'status', 'raw_payload'])]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $expense) {
            $expense->search_text = SearchNormalizer::normalize((string) $expense->description);
        });

        // An échéance-generated expense keeps a link back to its occurrence. Deleting the
        // expense directly (outside the échéance flow) must not leave that occurrence stuck
        // as "generated" forever — cancel it instead, so it stops counting toward the total.
        static::deleting(function (self $expense) {
            $occurrence = $expense->echeanceOccurrence;

            if ($occurrence === null) {
                return;
            }

            $occurrence->update(['status' => EcheanceOccurrenceStatus::Cancelled, 'expense_id' => null]);
            $occurrence->echeance()->decrement('occurrences_generated');
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'date' => 'date',
            'status' => ExpenseStatus::class,
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

    public function echeanceOccurrence(): HasOne
    {
        return $this->hasOne(EcheanceOccurrence::class);
    }
}
