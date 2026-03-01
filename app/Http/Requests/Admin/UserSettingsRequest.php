<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserSettingsRequest extends FormRequest
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
            'preference_planet_sort' => ['required', 'integer', 'min:0', 'max:4'],
            'preference_planet_sort_sequence' => ['required', 'integer', 'min:0', 'max:1'],
            'preference_spy_probes' => ['required', 'integer', 'min:0'],
            'preference_vacations_status' => ['nullable', 'string'],
            'preference_delete_mode' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        /** @var User $user */
        $user = $this->route('user');

        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.users.settings', $user->id));
    }
}
