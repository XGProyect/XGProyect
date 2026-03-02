<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PlanetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'initial_fields' => ['nullable', 'integer', 'min:0'],
            'metal_basic_income' => ['nullable', 'integer', 'min:0'],
            'crystal_basic_income' => ['nullable', 'integer', 'min:0'],
            'deuterium_basic_income' => ['nullable', 'integer', 'min:0'],
            'energy_basic_income' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.planets'));
    }
}
