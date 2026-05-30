<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BotsRequest extends FormRequest
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
            'amount' => ['required', 'integer', 'min:1', 'max:1000'],
            'galaxy_from' => ['nullable', 'integer', 'min:1', 'max:' . MAX_GALAXY_IN_WORLD],
            'galaxy_to' => ['nullable', 'integer', 'min:1', 'max:' . MAX_GALAXY_IN_WORLD, 'gte:galaxy_from'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.bots'));
    }
}
