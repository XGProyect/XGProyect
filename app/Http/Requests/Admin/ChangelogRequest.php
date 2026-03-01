<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ChangelogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'changelog_language' => (int) $this->string('changelog_language')->value(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'changelog_date' => ['required', 'date_format:Y-m-d'],
            'changelog_version' => [
                'required',
                'string',
                'regex:/^(0|[1-9]\d*)\.((0|[1-9]\d*)\.)?(0|[1-9]\d*)(-(0|[1-9]\d*|\d*[a-zA-Z][0-9a-zA-Z]*))?$/',
            ],
            'changelog_language' => ['required', 'integer', 'min:1', 'exists:languages,id'],
            'text' => ['required', 'string'],
        ];
    }

    /**
     * @param string|null $key
     * @param mixed $default
     *
     * @return array{changelog_date: string, changelog_version: string, changelog_language: int, text: string}
     */
    public function validated($key = null, $default = null): array
    {
        /** @var array{changelog_date: string, changelog_version: string, changelog_language: int, text: string} */
        return parent::validated($key, $default);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        /** @var array<string, string> */
        return [
            'changelog_date' => __('admin/changelog.ch_date'),
            'changelog_version' => __('admin/changelog.ch_version'),
            'changelog_language' => __('admin/changelog.ch_language'),
            'text' => __('admin/changelog.ch_description'),
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
