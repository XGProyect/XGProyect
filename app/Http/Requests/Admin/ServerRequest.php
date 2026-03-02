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
        $settings = [];

        // Identity
        if ($this->has('game_name')) {
            $settings['game_name'] = $this->string('game_name')->toString();
        }
        if ($this->has('game_logo')) {
            $settings['game_logo'] = $this->string('game_logo')->toString();
        }
        if ($this->has('language')) {
            $settings['lang'] = $this->string('language')->toString();
        }
        if ($this->has('admin_email')) {
            $settings['admin_email'] = $this->string('admin_email')->toString();
        }
        if ($this->has('forum_url')) {
            $settings['forum_url'] = UrlHelper::prepUrl($this->string('forum_url')->toString());
        }

        // Speed & Economy — stored as raw value × 2500
        if ($this->has('game_speed')) {
            $settings['game_speed'] = 2500 * $this->integer('game_speed');
        }
        if ($this->has('fleet_speed')) {
            $settings['fleet_speed'] = 2500 * $this->integer('fleet_speed');
        }
        if ($this->has('resource_multiplier')) {
            $settings['resource_multiplier'] = $this->integer('resource_multiplier');
        }

        // Server Access — booleans written as 0/1; close_reason always written when present
        $settings['game_enable'] = $this->boolean('game_enable') ? 1 : 0;
        if ($this->has('close_reason')) {
            $settings['close_reason'] = addslashes($this->string('close_reason')->toString());
        }

        // Date & Time
        if ($this->has('date_time_zone')) {
            $settings['date_time_zone'] = $this->string('date_time_zone')->toString();
        }
        if ($this->has('date_format')) {
            $settings['date_format'] = $this->string('date_format')->toString();
        }
        if ($this->has('date_format_extended')) {
            $settings['date_format_extended'] = $this->string('date_format_extended')->toString();
        }

        // Combat Rules
        $settings['adm_attack'] = $this->boolean('adm_attack') ? 1 : 0;
        if ($this->has('fleet_cdr')) {
            $settings['fleet_cdr'] = $this->integer('fleet_cdr');
        }
        if ($this->has('defs_cdr')) {
            $settings['defs_cdr'] = $this->integer('defs_cdr');
        }

        // Noob Protection
        $settings['noobprotection'] = $this->boolean('noobprotection') ? 1 : 0;
        if ($this->has('noobprotectiontime')) {
            $settings['noobprotectiontime'] = $this->integer('noobprotectiontime');
        }
        if ($this->has('noobprotectionmulti')) {
            $settings['noobprotectionmulti'] = $this->integer('noobprotectionmulti');
        }

        return $settings;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.server'));
    }
}
