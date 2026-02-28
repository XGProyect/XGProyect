<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AllianceRankRequest extends FormRequest
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
        if ($this->filled('create_rank')) {
            return [
                'rank_name' => ['required', 'string', 'min:1', 'max:50'],
            ];
        }

        return [];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'rank_name' => __('admin/alliances.al_rank_name'),
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
