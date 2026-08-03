<?php

declare(strict_types=1);

namespace App\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;

trait HasIncomeRules
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    protected function incomeRules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:1'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
