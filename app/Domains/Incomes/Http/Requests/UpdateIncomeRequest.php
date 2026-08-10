<?php

declare(strict_types=1);

namespace App\Domains\Incomes\Http\Requests;

use App\Domains\Incomes\Http\Requests\Concerns\HasIncomeRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateIncomeRequest extends FormRequest
{
    use HasIncomeRules;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->incomeRules();
    }
}
