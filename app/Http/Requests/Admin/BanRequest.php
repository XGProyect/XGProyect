<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BanRequest extends FormRequest
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
        if ($this->isMethod('post') && $this->filled('bannow')) {
            return [
                'ban_name' => 'required|string|exists:users,name',
                'days' => 'required|integer|min:0|max:36500',
                'hour' => 'required|integer|min:0|max:23',
                'text' => 'nullable|string|max:500',
                'vacat' => 'nullable|string',
            ];
        }

        return [
            'ban_name' => 'required|string|exists:users,name',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ban_name' => __('admin/ban.bn_username'),
            'days' => __('admin/ban.bn_time_days'),
            'hour' => __('admin/ban.bn_time_hours'),
            'text' => __('admin/ban.bn_reason'),
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
