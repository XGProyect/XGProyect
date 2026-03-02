<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Xgp\App\Helpers\UrlHelper;

class ServerRequest extends FormRequest
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
            // Identity
            'game_name' => ['nullable', 'string', 'max:60'],
            'game_logo' => ['nullable', 'string', 'max:254'],
            'language' => ['nullable', 'string'],
            'admin_email' => ['nullable', 'email', 'max:254'],
            'forum_url' => ['nullable', 'url', 'max:254'],

            // Speed & Economy
            'game_speed' => ['nullable', 'integer', 'min:1', 'max:100'],
            'fleet_speed' => ['nullable', 'integer', 'min:1', 'max:100'],
            'resource_multiplier' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Server Access
            'game_enable' => ['nullable'],
            'close_reason' => ['nullable', 'string'],

            // Date & Time
            'date_time_zone' => ['nullable', 'timezone'],
            'date_format' => ['nullable', 'string', 'max:30'],
            'date_format_extended' => ['nullable', 'string', 'max:30'],

            // Combat Rules
            'adm_attack' => ['nullable'],
            'fleet_cdr' => ['nullable', 'integer', 'min:0', 'max:100'],
            'defs_cdr' => ['nullable', 'integer', 'min:0', 'max:100'],

            // Noob Protection
            'noobprotection' => ['nullable'],
            'noobprotectiontime' => ['nullable', 'integer', 'min:0'],
            'noobprotectionmulti' => ['nullable', 'integer', 'min:0', 'max:99'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toSettings(): array
    {
        $validated = $this->validated();
        $settings = [];

        // Identity
        if (isset($validated['game_name'])) {
            $settings['game_name'] = $validated['game_name'];
        }
        if (isset($validated['game_logo'])) {
            $settings['game_logo'] = $validated['game_logo'];
        }
        if (isset($validated['language'])) {
            $settings['lang'] = $validated['language'];
        }
        if (isset($validated['admin_email'])) {
            $settings['admin_email'] = $validated['admin_email'];
        }
        if (isset($validated['forum_url'])) {
            $settings['forum_url'] = UrlHelper::prepUrl($validated['forum_url']);
        }

        // Speed & Economy — stored as raw value × 2500
        if (isset($validated['game_speed'])) {
            $settings['game_speed'] = 2500 * $validated['game_speed'];
        }
        if (isset($validated['fleet_speed'])) {
            $settings['fleet_speed'] = 2500 * $validated['fleet_speed'];
        }
        if (isset($validated['resource_multiplier'])) {
            $settings['resource_multiplier'] = $validated['resource_multiplier'];
        }

        // Server Access — booleans written as 0/1; close_reason always written when present
        $settings['game_enable'] = $this->boolean('game_enable') ? 1 : 0;
        if ($this->has('close_reason')) {
            $settings['close_reason'] = addslashes((string) $validated['close_reason']);
        }

        // Date & Time
        if (isset($validated['date_time_zone'])) {
            $settings['date_time_zone'] = $validated['date_time_zone'];
        }
        if (isset($validated['date_format'])) {
            $settings['date_format'] = $validated['date_format'];
        }
        if (isset($validated['date_format_extended'])) {
            $settings['date_format_extended'] = $validated['date_format_extended'];
        }

        // Combat Rules
        $settings['adm_attack'] = $this->boolean('adm_attack') ? 1 : 0;
        if (isset($validated['fleet_cdr'])) {
            $settings['fleet_cdr'] = $validated['fleet_cdr'];
        }
        if (isset($validated['defs_cdr'])) {
            $settings['defs_cdr'] = $validated['defs_cdr'];
        }

        // Noob Protection
        $settings['noobprotection'] = $this->boolean('noobprotection') ? 1 : 0;
        if (isset($validated['noobprotectiontime'])) {
            $settings['noobprotectiontime'] = $validated['noobprotectiontime'];
        }
        if (isset($validated['noobprotectionmulti'])) {
            $settings['noobprotectionmulti'] = $validated['noobprotectionmulti'];
        }

        return $settings;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.server'));
    }
}
