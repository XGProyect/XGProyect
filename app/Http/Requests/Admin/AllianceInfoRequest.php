<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Alliance;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AllianceInfoRequest extends FormRequest
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
        /** @var Alliance $alliance */
        $alliance = $this->route('alliance');
        $allianceId = $alliance->alliance_id;

        return [
            'alliance_name' => [
                'required',
                'string',
                'min:3',
                'max:30',
                Rule::unique('alliance', 'alliance_name')->ignore($allianceId, 'alliance_id'),
            ],
            'alliance_tag' => [
                'required',
                'string',
                'min:3',
                'max:8',
                Rule::unique('alliance', 'alliance_tag')->ignore($allianceId, 'alliance_id'),
            ],
            'alliance_owner' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'alliance_web' => ['nullable', 'string', 'max:255'],
            'alliance_image' => ['nullable', 'string', 'max:255'],
            'alliance_description' => ['nullable', 'string'],
            'alliance_text' => ['nullable', 'string'],
            'alliance_request' => ['nullable', 'string'],
            'alliance_request_notallow' => ['required', 'integer', Rule::in([0, 1])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        /** @var string $name */
        $name = __('admin/alliances.al_alliance_information_name');
        /** @var string $tag */
        $tag = __('admin/alliances.al_alliance_information_tag');
        /** @var string $owner */
        $owner = __('admin/alliances.al_alliance_information_owner');

        return [
            'alliance_name' => $name,
            'alliance_tag' => $tag,
            'alliance_owner' => $owner,
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $errorMessages = $validator->errors()->all();

        session()->flash('warning', implode('<br>', $errorMessages));

        throw (new ValidationException($validator))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
