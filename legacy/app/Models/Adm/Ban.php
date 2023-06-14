<?php

declare(strict_types=1);

namespace Xgp\App\Models\Adm;

use Exception;
use Xgp\App\Core\Model;

class Ban extends Model
{
    public function unbanUser(string $username): void
    {
        $clean_username = $this->db->escapeValue($username);

        $this->db->query(
            'DELETE FROM `' . BANNED . "`
            WHERE `banned_who` = '" . $clean_username . "'"
        );

        $this->db->query(
            'UPDATE `' . USERS . "` SET
                `banned` = '0'
            WHERE `name` = '" . $clean_username . "'
            LIMIT 1"
        );
    }

    /**
     * Get banned user data
     *
     * @param string $ban_name
     * @return array|null
     */
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
            WHERE `banned_who` = '" . $clean_user_name . "'"
        );
    }

    public function setOrUpdateBan(?array $banned_user, array $ban_data, ?string $vacation_mode): void
    {
        try {
            $this->db->beginTransaction();

            if (isset($banned_user)) {
                $this->db->query(
                    'UPDATE `' . BANNED . "`  SET
                        `banned_who` = '" . $ban_data['ban_name'] . "',
                        `banned_theme` = '" . $ban_data['ban_reason'] . "',
                        `banned_time` = '" . $ban_data['ban_time'] . "',
                        `banned_longer` = '" . $ban_data['ban_until'] . "',
                        `banned_author` = '" . $ban_data['ban_author'] . "',
                        `banned_email` = '" . $ban_data['ban_author_email'] . "'
                    WHERE `banned_who` = '" . $ban_data['ban_name'] . "';"
                );
            } else {
                $this->db->query(
                    'INSERT INTO `' . BANNED . "` SET
                        `banned_who` = '" . $ban_data['ban_name'] . "',
                        `banned_theme` = '" . $ban_data['ban_reason'] . "',
                        `banned_time` = '" . $ban_data['ban_time'] . "',
                        `banned_longer` = '" . $ban_data['ban_until'] . "',
                        `banned_author` = '" . $ban_data['ban_author'] . "',
                        `banned_email` = '" . $ban_data['ban_author_email'] . "';"
                );
            }

            $userId = $this->db->queryFetch(
                'SELECT
                    `id`
                FROM `' . USERS . "`
                WHERE `name` = '" . $ban_data['ban_name'] . "' LIMIT 1"
            )['id'];

            $this->db->query(
                'UPDATE `' . USERS . '` AS u, `' . PREFERENCES . '` AS pr, `' . PLANETS . "` AS p SET
                    u.`banned` = '" . $ban_data['ban_until'] . "',
                    pr.`preference_vacation_mode` = " . (isset($vacation_mode) && $vacation_mode != '' ? "'" . time() . "'" : 'NULL') . ",
                    p.`planet_building_metal_mine_percent` = '0',
                    p.`planet_building_crystal_mine_percent` = '0',
                    p.`planet_building_deuterium_sintetizer_percent` = '0',
                    p.`planet_building_solar_plant_percent` = '0',
                    p.`planet_building_fusion_reactor_percent` = '0',
                    p.`planet_ship_solar_satellite_percent` = '0'
                WHERE u.`id` = " . $userId . '
                        AND pr.`preference_user_id` = ' . $userId . '
                        AND p.`planet_user_id` = ' . $userId . ';'
            );

            $this->db->commitTransaction();
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
        }
    }

    /**
     * Get list of users based on the provided conditions
     *
     * @param string $where_authlevel
     * @param string $where_banned
     * @param string $query_order
     * @return array
     */
    public function getListOfUsers(string $where_authlevel, string $where_banned, string $query_order): array
    {
        return $this->db->queryFetchAll(
            'SELECT
                `id`,
                `name`,
                `banned`
            FROM `' . USERS . '`
            ' . $where_authlevel . ' ' . $where_banned . '
            ORDER BY ' . $query_order . ' ASC'
        );
    }

    public function getBannedUsers(string $order): ?array
    {
        return $this->db->queryFetchAll(
            'SELECT
                `id`,
                `name`
            FROM `' . USERS . "`
            WHERE `banned` <> '0'
            ORDER BY " . $order . ' ASC'
        );
    }
}
