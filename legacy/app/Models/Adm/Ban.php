<?php

declare(strict_types=1);

namespace Xgp\App\Models\Adm;

use App\Models\User;
use Exception;
use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Ban extends Model
{
    public function getBannedUserData(string $ban_name): ?array
    {
        $clean_user_name = $this->db->escapeValue($ban_name);

        return $this->db->queryFetch(
            'SELECT
                b.*,
                p.`preference_user_id`,
                p.`preference_vacation_mode`
            FROM `' . BANNED . '` AS b
            INNER JOIN `' . PREFERENCES . '` AS p
                ON p.`preference_user_id` = (
                    SELECT
                        `id`
                    FROM `' . USERS . "`
                    WHERE `name` = '" . $clean_user_name . "'
                    LIMIT 1
                )
            WHERE `user_id` = (
                SELECT
                    `id`
                FROM `" . USERS . "`
                WHERE `name` = '" . $clean_user_name . "'
                LIMIT 1
            )"
        );
    }

    public function setOrUpdateBan(?array $bannedUser, array $banData, ?string $vacationMode): void
    {
        try {
            $this->db->beginTransaction();

            $userId = User::where('name', $banData['user_name'])->value('id');

            if (isset($bannedUser)) {
                \App\Models\Ban::where('user_id', $userId)->update([
                    'admin_id' => $banData['admin_id'],
                    'details' => $banData['details'],
                    'until' => $banData['until'],
                ]);
            } else {
                \App\Models\Ban::create([
                    'user_id' => $userId,
                    'admin_id' => $banData['admin_id'],
                    'details' => $banData['details'],
                    'until' => $banData['until'],
                ]);
            }

            $this->db->query(
                'UPDATE `' . PREFERENCES . '` AS pr, `' . PLANETS . '` AS p SET
                    pr.`preference_vacation_mode` = ' . (isset($vacationMode) && $vacationMode != '' ? "'" . time() . "'" : 'NULL') . ",
                    p.`planet_building_metal_mine_percent` = '0',
                    p.`planet_building_crystal_mine_percent` = '0',
                    p.`planet_building_deuterium_sintetizer_percent` = '0',
                    p.`planet_building_solar_plant_percent` = '0',
                    p.`planet_building_fusion_reactor_percent` = '0',
                    p.`planet_ship_solar_satellite_percent` = '0'
                WHERE pr.`preference_user_id` = " . $userId . '
                        AND p.`planet_user_id` = ' . $userId . ';'
            );

            $this->db->commitTransaction();
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
        }
    }

    public function getListOfUsers(string $where_authlevel, string $where_banned, string $query_order): array
    {
        return $this->db->queryFetchAll(
            'SELECT
                u.`id`,
                u.`name`,
                b.`until`
            FROM `' . USERS . '` AS u
            LEFT JOIN `' . BANNED . '` AS b ON b.user_id = u.id
            ' . $where_authlevel . ' ' . $where_banned . '
            ORDER BY ' . $query_order . ' ASC'
        );
    }

    public function getBannedUsers(string $order): array
    {
        return $this->db->queryFetchAll(
            'SELECT
                u.`id`,
                u.`name`
            FROM `' . BANNED . '` AS b
            INNER JOIN `' . USERS . '` AS u ON u.id = b.user_id
            ORDER BY ' . $order . ' ASC'
        );
    }
}
