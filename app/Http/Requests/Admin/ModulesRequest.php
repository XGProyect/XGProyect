<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Services\SettingsService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ModulesRequest extends FormRequest
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
        $count = count(explode(';', $this->settings()->getString('modules')));
        $rules = [];

        for ($i = 0; $i < $count; $i++) {
            $rules["status{$i}"] = ['nullable'];
        }

        return $rules;
    }

    /**
     * @return array<int, int>
     */
    public function toValues(): array
    {
        $count = count(explode(';', $this->settings()->getString('modules')));
        $values = [];

        for ($i = 0; $i < $count; $i++) {
            $values[] = $this->has("status{$i}") ? 1 : 0;
        }

        return $values;
    }

    protected function failedValidation(Validator $validator): void
    {
        session()->flash('danger', implode('<br>', $validator->errors()->all()));

        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.modules'));
    }

    private function settings(): SettingsService
    {
        return $this->container->make(SettingsService::class);
    }
}
