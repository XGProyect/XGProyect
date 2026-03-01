<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserPremiumRequest extends FormRequest
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
        $rules = [
            'premium_dark_matter' => ['nullable', 'integer', 'min:0'],
        ];

        foreach ($this->all() as $key => $value) {
            if (str_starts_with($key, 'premium_') && $key !== 'premium_dark_matter') {
                $rules[$key] = ['nullable', 'integer', 'min:0', 'max:3'];
            }
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        /** @var User $user */
        $user = $this->route('user');

        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.users.premium', $user->id));
    }
}
