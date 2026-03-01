<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');
        $userId = $user->id;

        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:64',
                Rule::unique('users', 'name')->ignore($userId, 'id'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId, 'id'),
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'authlevel' => ['required', 'integer', 'min:0', 'max:3'],
            'home_planet_id' => ['required', 'integer', 'min:1', 'exists:planets,planet_id'],
            'current_planet' => ['required', 'integer', 'min:1', 'exists:planets,planet_id'],
            'ally_id' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => (string) __('admin/users.us_user_information_username'),
            'email' => (string) __('admin/users.us_user_information_email'),
            'authlevel' => (string) __('admin/users.us_user_information_level'),
            'home_planet_id' => (string) __('admin/users.us_user_information_pp'),
            'current_planet' => (string) __('admin/users.us_user_information_ap'),
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        /** @var User $user */
        $user = $this->route('user');

        session()->flashInput($this->input());

        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.users.info', $user->id));
    }
}
