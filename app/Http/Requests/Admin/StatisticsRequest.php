<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;

class StatisticsRequest extends FormRequest
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
            'stat_points' => ['nullable', 'integer', 'min:1'],
            'stat_update_time' => ['nullable', 'integer', 'min:1'],
            'stat_admin_level' => ['nullable', 'integer', 'min:' . UserRanks::PLAYER, 'max:' . UserRanks::ADMIN],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.statistics'));
    }
}
