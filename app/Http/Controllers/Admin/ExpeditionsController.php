<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ExpeditionsRequest;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ExpeditionsController extends AdminSettingsController
{
    public function __construct(SettingsService $settings)
    {
        parent::__construct($settings);
    }

    public function index(): View
    {
        $groups = $this->buildGroups();

        return $this->view('admin.expeditions', [
            'primaryGroups' => array_intersect_key($groups, ['results' => true]),
            'secondaryGroups' => array_diff_key($groups, ['results' => true]),
        ]);
    }

    public function update(ExpeditionsRequest $request): RedirectResponse
    {
        foreach ($request->toSettings() as $key => $value) {
            $this->settings->write($key, $value);
        }

        return $this->saved('admin.expeditions', 'admin/expeditions.ex_all_ok_message');
    }

    /**
     * @return array<string, array{title: string, icon: string, fields: array<int, array{name: string, label: string, value: string}>}>
     */
    private function buildGroups(): array
    {
        return [
            'results' => [
                'title' => __('admin/expeditions.ex_section_results'),
                'icon' => 'fas fa-random',
                'fields' => $this->fields([
                    'expedition_result_dark_matter_weight' => __('admin/expeditions.ex_result_dark_matter'),
                    'expedition_result_ships_weight' => __('admin/expeditions.ex_result_ships'),
                    'expedition_result_resources_weight' => __('admin/expeditions.ex_result_resources'),
                    'expedition_result_pirates_weight' => __('admin/expeditions.ex_result_pirates'),
                    'expedition_result_aliens_weight' => __('admin/expeditions.ex_result_aliens'),
                    'expedition_result_delay_weight' => __('admin/expeditions.ex_result_delay'),
                    'expedition_result_early_weight' => __('admin/expeditions.ex_result_early'),
                    'expedition_result_nothing_weight' => __('admin/expeditions.ex_result_nothing'),
                    'expedition_result_merchant_weight' => __('admin/expeditions.ex_result_merchant'),
                    'expedition_result_black_hole_weight' => __('admin/expeditions.ex_result_black_hole'),
                ]),
            ],
            'darkMatter' => [
                'title' => __('admin/expeditions.ex_section_dark_matter'),
                'icon' => 'fas fa-gem',
                'fields' => $this->fields([
                    'expedition_dark_matter_source_small_weight' => __('admin/expeditions.ex_package_small'),
                    'expedition_dark_matter_source_medium_weight' => __('admin/expeditions.ex_package_medium'),
                    'expedition_dark_matter_source_large_weight' => __('admin/expeditions.ex_package_large'),
                ]),
            ],
            'resourceTypes' => [
                'title' => __('admin/expeditions.ex_section_resource_types'),
                'icon' => 'fas fa-boxes',
                'fields' => $this->fields([
                    'expedition_resource_type_metal_weight' => __('admin/expeditions.ex_resource_metal'),
                    'expedition_resource_type_crystal_weight' => __('admin/expeditions.ex_resource_crystal'),
                    'expedition_resource_type_deuterium_weight' => __('admin/expeditions.ex_resource_deuterium'),
                ]),
            ],
            'resourceSizes' => [
                'title' => __('admin/expeditions.ex_section_resource_sizes'),
                'icon' => 'fas fa-layer-group',
                'fields' => $this->fields([
                    'expedition_resource_source_normal_weight' => __('admin/expeditions.ex_package_normal'),
                    'expedition_resource_source_large_weight' => __('admin/expeditions.ex_package_large'),
                    'expedition_resource_source_xl_weight' => __('admin/expeditions.ex_package_xl'),
                ]),
            ],
            'fleetDelays' => [
                'title' => __('admin/expeditions.ex_section_fleet_delay'),
                'icon' => 'fas fa-clock',
                'fields' => $this->fields([
                    'expedition_fleet_delay_2_weight' => __('admin/expeditions.ex_delay_2'),
                    'expedition_fleet_delay_3_weight' => __('admin/expeditions.ex_delay_3'),
                    'expedition_fleet_delay_5_weight' => __('admin/expeditions.ex_delay_5'),
                ]),
            ],
        ];
    }

    /**
     * @param array<string, string> $labels
     *
     * @return array<int, array{name: string, label: string, value: string}>
     */
    private function fields(array $labels): array
    {
        $fields = [];

        foreach ($labels as $name => $label) {
            $fields[] = [
                'name' => $name,
                'label' => $label,
                'value' => $this->formatPercentage($this->settings->getInt($name)),
            ];
        }

        return $fields;
    }

    private function formatPercentage(int $basisPoints): string
    {
        return rtrim(rtrim(number_format($basisPoints / 100, 2, '.', ''), '0'), '.');
    }
}
