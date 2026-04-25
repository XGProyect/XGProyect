<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use App\Services\TimingService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\PreferencesEnumerator as PrefEnum;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\Preferences as Pref;
use Xgp\App\Libraries\Users;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class PreferencesController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private ?Pref $preferences = null;
    private array $fields_to_update = [];
    private string $error = '';
    private bool $post = false;

    public function __construct(
        private FormatService $formatService,
        private TimingService $timingService,
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Options));

        $this->user = Users::getInstance()->getUserData();

        $this->setUpPreferences();
        $this->runAction();

        Template::legacyView(
            'preferences.view',
            array_merge(
                $this->setMessageDisplay(),
                $this->setUserData(),
                [
                    'preference_spy_probes' => $this->preferences->getCurrentPreference()->getPreferenceSpyProbes(),
                    'sort_planet' => $this->sortPlanetOptions(),
                    'sort_sequence' => $this->sortSequenceOptions(),
                ],
                $this->setVacationMode(),
                $this->setDeleteMode()
            )
        );
    }

    private function setUpPreferences(): void
    {
        $this->preferences = new Pref(
            array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT p.* FROM `' . PREFERENCES . "` p WHERE p.`preference_user_id` = '" . (int) $this->user['id'] . "';"
                    )
                )
            ),
            (int) $this->user['id']
        );
    }

    private function runAction(): void
    {
        $vacation_mode = filter_input(INPUT_POST, 'preference_vacation_mode');

        if ($vacation_mode) {
            $this->post = true;

            $this->validateVacationMode();
        }

        $preferences = filter_input_array(INPUT_POST, [
            'new_user_name' => FILTER_UNSAFE_RAW,
            'confirmation_user_password' => FILTER_UNSAFE_RAW,
            'current_user_password' => FILTER_UNSAFE_RAW,
            'new_user_password' => FILTER_UNSAFE_RAW,
            'new_user_email' => FILTER_VALIDATE_EMAIL,
            'confirmation_email_password' => FILTER_UNSAFE_RAW,
            'preference_spy_probes' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['default' => 1, 'min_range' => 1, 'max_range' => 99],
            ],
            'preference_planet_sort' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['default' => 0, 'min_range' => 0, 'max_range' => (count(PrefEnum::order) - 1)],
            ],
            'preference_planet_sort_sequence' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['default' => 0, 'min_range' => 0, 'max_range' => (count(PrefEnum::sequence) - 1)],
            ],
            'preference_delete_mode' => FILTER_UNSAFE_RAW,
        ]);

        if ($preferences) {
            $this->post = true;

            $this->validateDeleteMode($preferences);

            // remove values that din't pass the validation
            $preferences = array_diff($preferences, [null, false]);

            // run validations
            $this->validateNewUserName($preferences);
            $this->validateNewPassword($preferences);
            $this->validateNewEmail($preferences);
            $this->validateSpyProbes($preferences);
            $this->validatePlanetSort($preferences);
            $this->validatePlanetSortSequence($preferences);

            if ($this->error == '') {
                $columns_to_update = [];
                foreach ($this->fields_to_update as $column => $value) {
                    if (strpos($column, 'preference_') === false) {
                        $columns_to_update[] = 'u.`' . $column . "` = '" . $value . "'";
                    }
                    if (strpos($column, 'preference_') !== false) {
                        $columns_to_update[] = 'p.`' . $column . '` = ' . (is_null($value) ? 'NULL' : "'" . $value . "'");
                    }
                }
                DB::statement(
                    $this->prepareSql(
                        'UPDATE ' . USERS . ' AS u, ' . PREFERENCES . ' AS p SET
                        ' . join(', ', $columns_to_update) . "
                        WHERE u.`id` = '" . (int) $this->user['id'] . "'
                            AND p.`preference_user_id` = '" . (int) $this->user['id'] . "';"
                    )
                );

                $this->setUpPreferences();
            }
        }
    }

    private function setMessageDisplay(): array
    {
        $message = [
            'color' => '',
            'message' => '',
        ];

        if ($this->post) {
            $message = [
                'color' => ($this->error == '' ? '#00ff00' : '#ff0000'),
                'message' => ($this->error == '' ? __('game/preferences.pr_ok_settings_saved') : $this->error),
            ];
        }

        return $message;
    }

    private function setUserData(): array
    {
        return [
            'name' => $this->user['name'],
            'hide_nickname_change' => ($this->preferences->isNickNameChangeAllowed() ? '' : 'style="display: none"'),
            'email' => $this->user['email'],
        ];
    }

    private function sortPlanetOptions(): array
    {
        $order_options = [];

        foreach (PrefEnum::order as $order => $value) {
            $order_options[] = [
                'value' => $value,
                'selected' => (
                    $value == $this->preferences->getCurrentPreference()->getPreferencePlanetSort() ? 'selected="selected"' : ''
                ),
                'text' => __('game/preferences.pr_order_' . $order),
            ];
        }

        return $order_options;
    }

    private function sortSequenceOptions(): array
    {
        $sequence_options = [];

        foreach (PrefEnum::sequence as $sequence => $value) {
            $sequence_options[] = [
                'value' => $value,
                'selected' => (
                    $value == $this->preferences->getCurrentPreference()->getPreferencePlanetSortSequence() ? 'selected="selected"' : ''
                ),
                'text' => __('game/preferences.pr_sorting_sequence_' . $sequence),
            ];
        }

        return $sequence_options;
    }

    private function setVacationMode(): array
    {
        if ($this->preferences->isVacationModeOn()) {
            return [
                'hide_no_vacation' => '',
                'pr_vacation_mode_active' => $this->formatService->strongText(
                    $this->formatService->colorRed(__('game/preferences.pr_vacation_mode_active'))
                ),
                'hide_vacation_invalid' => 'style="display: none"',
                'disabled' => ($this->preferences->isVacationModeRemovalAllowed() ? '' : 'style="display: none"'),
            ];
        }

        if ($this->isEmpireActive()) {
            return [
                'hide_no_vacation' => '',
                'pr_vacation_mode_active' => $this->formatService->strongText(
                    $this->formatService->colorRed(__('game/preferences.pr_empire_active') . __('game/preferences.pr_empire_active_fleet'))
                ),
                'hide_vacation_invalid' => '',
                'disabled' => 'style="display: none"',
            ];
        }

        return [
            'hide_no_vacation' => 'style="display: none"',
            'pr_vacation_mode_active' => '',
            'hide_vacation_invalid' => '',
            'disabled' => '',
        ];
    }

    private function setDeleteMode(): array
    {
        if ($this->preferences->getCurrentPreference()->getPreferenceDeleteMode() > 0) {
            return [
                'pr_delete_account' => $this->formatService->colorRed(strtr(
                    __('game/preferences.pr_delete_mode_active'),
                    [
                        '%s' => $this->timingService->formatExtendedDate(
                            $this->preferences->getCurrentPreference()->getPreferenceDeleteMode() + ONE_WEEK
                        ),
                    ]
                )),
                'preference_delete_mode' => 'checked="checked"',
                'hide_delete' => 'style="display: none"',
            ];
        }

        return [
            'preference_delete_mode' => '',
            'hide_delete' => '',
        ];
    }

    private function validateNewUserName(array $preferences): void
    {
        if (isset($preferences['new_user_name']) &&
            isset($preferences['confirmation_user_password']) &&
            $this->preferences->isNickNameChangeAllowed()) {
            if (password_verify($preferences['confirmation_user_password'], $this->user['password'])) {
                $username_len = strlen(trim($preferences['new_user_name']));

                if ($username_len > 3 && $username_len <= 20) {
                    $nickRow = DB::selectOne(
                        $this->prepareSql('SELECT `id` FROM `' . USERS . '` WHERE `name` = ? LIMIT 1;'),
                        [$preferences['new_user_name']]
                    );
                    if (!($nickRow !== null ? (array) $nickRow : [])) {
                        $this->fields_to_update['name'] = $preferences['new_user_name'];
                        $this->fields_to_update['preference_nickname_change'] = time();
                    } else {
                        $this->error = __('game/preferences.pr_error_nick_in_use');
                    }
                } else {
                    $this->error = strtr(
                        __('game/preferences.pr_error_user_invalid_characters'),
                        ['%s' => $preferences['new_user_name']]
                    );
                }
            } else {
                $this->error = __('game/preferences.pr_error_wrong_password');
            }
        }
    }

    private function validateNewPassword(array $preferences): void
    {
        if (isset($preferences['current_user_password']) &&
            isset($preferences['new_user_password'])) {
            if (password_verify($preferences['current_user_password'], $this->user['password'])) {
                $this->fields_to_update['password'] = Functions::hash(trim($preferences['new_user_password']));
            } else {
                $this->error = __('game/preferences.pr_error_wrong_password');
            }
        }
    }

    private function validateNewEmail(array $preferences): void
    {
        if (isset($preferences['new_user_email']) &&
            isset($preferences['confirmation_email_password'])) {
            if (password_verify($preferences['confirmation_email_password'], $this->user['password'])) {
                $user_email_len = strlen(trim($preferences['new_user_email']));

                if ($user_email_len > 4 && $user_email_len <= 64) {
                    $emailRow = DB::selectOne(
                        $this->prepareSql('SELECT `email` FROM `' . USERS . '` WHERE `email` = ? LIMIT 1;'),
                        [$preferences['new_user_email']]
                    );
                    if (!($emailRow !== null ? (array) $emailRow : [])) {
                        $this->fields_to_update['email'] = $preferences['new_user_email'];
                    } else {
                        $this->error = __('game/preferences.pr_error_email_in_use');
                    }
                } else {
                    $this->error = strtr(
                        __('game/preferences.pr_error_email_invalid_characters'),
                        ['%s' => $preferences['new_user_email']]
                    );
                }
            } else {
                $this->error = __('game/preferences.pr_error_wrong_password');
            }
        }
    }

    private function validateSpyProbes(array $preferences): void
    {
        $this->fields_to_update['preference_spy_probes'] = $preferences['preference_spy_probes'];
    }

    private function validatePlanetSort(array $preferences): void
    {
        $this->fields_to_update['preference_planet_sort'] = $preferences['preference_planet_sort'];
    }

    private function validatePlanetSortSequence(array $preferences): void
    {
        $this->fields_to_update['preference_planet_sort_sequence'] = $preferences['preference_planet_sort_sequence'];
    }

    private function validateVacationMode(): void
    {
        if ($this->preferences->isVacationModeOn()) {
            if ($this->preferences->isVacationModeRemovalAllowed()) {
                $userId = (int) $this->user['id'];
                if ($userId > 0) {
                    DB::statement(
                        $this->prepareSql(
                            'UPDATE `' . PREFERENCES . '` pr, `' . PLANETS . "` p SET
                                pr.`preference_vacation_mode` = NULL,
                                p.`planet_last_update` = '" . time() . "',
                                p.`planet_building_metal_mine_percent` = '10',
                                p.`planet_building_crystal_mine_percent` = '10',
                                p.`planet_building_deuterium_sintetizer_percent` = '10',
                                p.`planet_building_solar_plant_percent` = '10',
                                p.`planet_building_fusion_reactor_percent` = '10',
                                p.`planet_ship_solar_satellite_percent` = '10'
                            WHERE pr.`preference_user_id` = '" . $userId . "'
                                AND p.`planet_user_id` = '" . $userId . "';"
                        )
                    );
                }
            }
        } else {
            if (!$this->isEmpireActive()) {
                $userId = (int) $this->user['id'];
                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . PREFERENCES . '` pr, `' . PLANETS . "` p SET
                            pr.`preference_vacation_mode` = '" . time() . "',
                            p.`planet_building_metal_mine_percent` = '0',
                            p.`planet_building_crystal_mine_percent` = '0',
                            p.`planet_building_deuterium_sintetizer_percent` = '0',
                            p.`planet_building_solar_plant_percent` = '0',
                            p.`planet_building_fusion_reactor_percent` = '0',
                            p.`planet_ship_solar_satellite_percent` = '0'
                        WHERE pr.`preference_user_id` = '" . $userId . "'
                            AND p.`planet_user_id` = '" . $userId . "';"
                    )
                );
            }
        }
    }

    private function isEmpireActive(): bool
    {
        $userId = (int) $this->user['id'];

        if ($userId > 0) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT (
                        (
                            SELECT
                                COUNT(f.`fleet_id`) AS quantity
                            FROM `' . FLEETS . "` f
                            WHERE f.`fleet_owner` = '" . $userId . "'
                        )
                    +
                        (
                            SELECT
                                COUNT(p.`planet_id`) AS quantity
                            FROM `" . PLANETS . "` p
                            WHERE p.`planet_user_id` = '" . $userId . "'
                                AND (p.`planet_b_building` <> 0
                                    OR `planet_b_tech` <> 0
                                    OR `planet_b_hangar` <> 0
                                )
                        )
                    ) as total"
                )
            );

            return $row !== null && $row->total > 0;
        }

        return false;
    }

    private function validateDeleteMode(array $preferences): void
    {
        if (isset($preferences['preference_delete_mode']) &&
            $preferences['preference_delete_mode'] == 'on') {
            $this->fields_to_update['preference_delete_mode'] = time();
        } else {
            $this->fields_to_update['preference_delete_mode'] = null;
        }
    }
}
