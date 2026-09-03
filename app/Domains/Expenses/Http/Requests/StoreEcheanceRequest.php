<?php

declare(strict_types=1);

namespace App\Domains\Expenses\Http\Requests;

use App\Domains\Expenses\Enums\EcheanceFrequency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreEcheanceRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id),
                ),
            ],
            'description' => ['required', 'string', 'max:255'],
            'frequency' => ['required', Rule::enum(EcheanceFrequency::class)],
            'occurrences_total' => ['nullable', 'integer', 'min:1', 'max:60'],
            'occurrences' => ['required', 'array', 'min:1', 'max:60'],
            'occurrences.*.date' => ['required', 'date'],
            'occurrences.*.amount' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $total = $this->integer('occurrences_total') ?: null;
            $count = count($this->array('occurrences'));
            $expected = $total ?? 1;

            if ($count !== $expected) {
                $validator->errors()->add(
                    'occurrences',
                    $total !== null
                        ? "Le nombre d'échéances saisies ({$count}) doit correspondre au nombre total ({$total})."
                        : "Une échéance sans nombre total ne doit avoir qu'une seule occurrence saisie.",
                );
            }
        });
    }
}
