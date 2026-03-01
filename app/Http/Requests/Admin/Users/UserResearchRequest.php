<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserResearchRequest extends FormRequest
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
        // Each research_* field must be a non-negative integer
        $rules = [];

        foreach ($this->all() as $key => $value) {
            if (str_starts_with($key, 'research_')) {
                $rules[$key] = ['required', 'integer', 'min:0'];
            }
        }

        return $rules;
    }

    protected function failedValidation(Validator $validator): void
    {
        /** @var User $user */
        $user = $this->route('user');

        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.users.research', $user->id));
    }
}
