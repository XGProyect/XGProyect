<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class LanguagesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'string'],
            'translations' => ['required', 'array'],
            'translations.*.key' => ['required', 'string'],
            'translations.*.value' => ['nullable', 'string'],
        ];
    }
}
