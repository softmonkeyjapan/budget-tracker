<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Requests;

use App\Domains\Expenses\Http\Requests\Concerns\HasExpenseRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreExpenseRequest extends FormRequest
{
    use HasExpenseRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->expenseRules();
    }
}
