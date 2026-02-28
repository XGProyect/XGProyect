<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AnnouncementRequest extends FormRequest
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
            'subject' => 'nullable|string|max:100',
            'color-picker' => 'nullable|string',
            'message' => 'nullable|string',
            'mail' => 'nullable|string',
            'text' => 'required|string|min:1|max:5000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'text.required' => __('admin/announcement.an_not_sent'),
            'text.min' => __('admin/announcement.an_not_sent'),
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
