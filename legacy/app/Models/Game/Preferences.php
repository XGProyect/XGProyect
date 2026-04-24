<?php

declare(strict_types=1);

namespace Xgp\App\Models\Game;

use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Preferences
{
    use PreparesLegacySql;

    public function getAllPreferencesByUserId(int $userId): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT p.* FROM `' . PREFERENCES . "` p WHERE p.`preference_user_id` = '" . $userId . "';"
                )
            )
        );
    }

    public function checkIfNicknameExists(string $nickname): array
    {
        $row = DB::selectOne(
            $this->prepareSql('SELECT `id` FROM `' . USERS . '` WHERE `name` = ? LIMIT 1;'),
            [$nickname]
        );

        return $row !== null ? (array) $row : [];
    }

    public function checkIfEmailExists(string $email): array
    {
        $row = DB::selectOne(
            $this->prepareSql('SELECT `email` FROM `' . USERS . '` WHERE `email` = ? LIMIT 1;'),
            [$email]
        );

        return $row !== null ? (array) $row : [];
    }

    public function updateValidatedFields(array $fields, int $userId): void
    {
        $columns_to_update = [];

        foreach ($fields as $column => $value) {
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
                WHERE u.`id` = '" . $userId . "'
                    AND p.`preference_user_id` = '" . $userId . "';"
            )
        );
    }

    public function isEmpireActive(int $userId): bool
    {
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

    public function startVacation(int $userId): bool
    {
        if (!$this->isEmpireActive($userId)) {
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

            return true;
        }

        return false;
    }

    public function endVacation(int $userId): void
    {
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
}
