<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Users;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UserPlanetRequest extends FormRequest
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
            // ── Core fields (both planet and moon forms) ──────────────────────
            'planet_name' => ['required', 'string', 'max:30'],
            'planet_user_id' => ['required', 'integer', 'exists:users,id'],
            'planet_image' => ['required', 'string'],
            'planet_diameter' => ['required', 'integer', 'min:0'],
            'planet_metal' => ['required', 'numeric', 'min:0'],
            'planet_crystal' => ['required', 'numeric', 'min:0'],
            'planet_deuterium' => ['required', 'numeric', 'min:0'],
            'planet_destroyed' => ['nullable', 'integer', 'in:0,1'],

            // ── Planet-only fields (not present on moon form) ─────────────────
            'planet_field_max' => ['nullable', 'integer', 'min:0'],
            'planet_temp_min' => ['nullable', 'integer'],
            'planet_temp_max' => ['nullable', 'integer'],
            'planet_energy_used' => ['nullable', 'integer'],

            // ── Production percent fields (planet-only) ───────────────────────
            'planet_building_metal_mine_percent' => ['nullable', 'integer', 'min:0', 'max:10'],
            'planet_building_crystal_mine_percent' => ['nullable', 'integer', 'min:0', 'max:10'],
            'planet_building_deuterium_sintetizer_percent' => ['nullable', 'integer', 'min:0', 'max:10'],
            'planet_building_solar_plant_percent' => ['nullable', 'integer', 'min:0', 'max:10'],
            'planet_building_fusion_reactor_percent' => ['nullable', 'integer', 'min:0', 'max:10'],
            'planet_ship_solar_satellite_percent' => ['nullable', 'integer', 'min:0', 'max:10'],

            // ── Optional debris fields ────────────────────────────────────────
            'planet_debris_metal' => ['nullable', 'numeric', 'min:0'],
            'planet_debris_crystal' => ['nullable', 'numeric', 'min:0'],

            // NOTE: planet_galaxy, planet_system, planet_planet, planet_type,
            // planet_field_current, planet_metal_perhour, planet_crystal_perhour,
            // planet_deuterium_perhour, planet_energy_max are read-only display
            // fields not submitted by the edit forms — excluded from validation.
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(url()->previous());
    }
}
