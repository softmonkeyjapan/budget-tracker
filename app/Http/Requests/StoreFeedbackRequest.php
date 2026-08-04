<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

final class StoreFeedbackRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['message' => trim((string) $this->input('message'))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:5000'],
            'page_url' => ['required', 'string', 'max:2048'],
        ];
    }
}
