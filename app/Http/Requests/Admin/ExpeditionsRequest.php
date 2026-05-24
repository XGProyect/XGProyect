<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator as ValidationContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class ExpeditionsRequest extends FormRequest
{
    /** @var array<string, list<string>> */
    public const GROUPS = [
        'results' => [
            'expedition_result_dark_matter_weight',
            'expedition_result_ships_weight',
            'expedition_result_resources_weight',
            'expedition_result_pirates_weight',
            'expedition_result_aliens_weight',
            'expedition_result_delay_weight',
            'expedition_result_early_weight',
            'expedition_result_nothing_weight',
            'expedition_result_merchant_weight',
            'expedition_result_black_hole_weight',
        ],
        'darkMatter' => [
            'expedition_dark_matter_source_small_weight',
            'expedition_dark_matter_source_medium_weight',
            'expedition_dark_matter_source_large_weight',
        ],
        'resourceTypes' => [
            'expedition_resource_type_metal_weight',
            'expedition_resource_type_crystal_weight',
            'expedition_resource_type_deuterium_weight',
        ],
        'resourceSizes' => [
            'expedition_resource_source_normal_weight',
            'expedition_resource_source_large_weight',
            'expedition_resource_source_xl_weight',
        ],
        'fleetDelays' => [
            'expedition_fleet_delay_2_weight',
            'expedition_fleet_delay_3_weight',
            'expedition_fleet_delay_5_weight',
        ],
    ];

    /** @var array<string, string> */
    private const GROUP_TRANSLATIONS = [
        'results' => 'admin/expeditions.ex_section_results',
        'darkMatter' => 'admin/expeditions.ex_section_dark_matter',
        'resourceTypes' => 'admin/expeditions.ex_section_resource_types',
        'resourceSizes' => 'admin/expeditions.ex_section_resource_sizes',
        'fleetDelays' => 'admin/expeditions.ex_section_fleet_delay',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->settingNames() as $setting) {
            $rules[$setting] = ['bail', 'required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d{1,2})?$/'];
        }

        return $rules;
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->count() > 0) {
                    return;
                }

                foreach (self::GROUPS as $group => $settings) {
                    $total = 0;

                    foreach ($settings as $setting) {
                        $total += $this->percentageToBasisPoints($this->input($setting));
                    }

                    if ($total !== 10000) {
                        $validator->errors()->add(
                            $settings[0],
                            __('admin/expeditions.ex_group_total_error', [
                                'group' => __(self::GROUP_TRANSLATIONS[$group]),
                            ])
                        );
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, int>
     */
    public function toSettings(): array
    {
        $settings = [];

        foreach ($this->settingNames() as $setting) {
            $settings[$setting] = $this->percentageToBasisPoints($this->input($setting));
        }

        return $settings;
    }

    /** @return list<string> */
    public static function settingNames(): array
    {
        return array_merge(...array_values(self::GROUPS));
    }

    private function percentageToBasisPoints(mixed $value): int
    {
        if (!is_scalar($value)) {
            return 0;
        }

        $percentage = trim((string) $value);

        if ($percentage === '') {
            return 0;
        }

        if (!str_contains($percentage, '.')) {
            return ((int) $percentage) * 100;
        }

        [$whole, $fraction] = explode('.', $percentage, 2);

        return (((int) $whole) * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    protected function failedValidation(ValidationContract $validator): void
    {
        throw ValidationException::withMessages($validator->errors()->toArray())
            ->redirectTo(route('admin.expeditions'));
    }
}
