<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AllianceMembersRequest extends FormRequest
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
            'delete_message' => ['nullable', 'array'],
            'delete_message.*' => ['nullable', 'string', 'in:on'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errorMessages = $validator->errors()->all();

        session()->flash('warning', $errorMessages[0]);

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
