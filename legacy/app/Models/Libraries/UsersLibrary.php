<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries;

use Xgp\App\Core\Model;
use Xgp\App\Core\Options;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class UsersLibrary extends Model
{
    public function getAllyIdByUserId(int $userId): array
    {
        return $this->db->queryFetch(
            'SELECT `ally_id` FROM `' . USERS . "` WHERE `id` = '" . $userId . "';"
        );
    }

    public function getAllianceDataByAllianceId(int $allianceId): array
    {
        return $this->db->queryFetch(
            'SELECT a.`alliance_id`, a.`alliance_ranks`,
                (SELECT COUNT(id) AS `ally_members`
                    FROM `' . USERS . "`
                    WHERE `ally_id` = '" . $allianceId . "') AS `ally_members`
            FROM `" . ALLIANCE . "` AS a
            WHERE a.`alliance_id` = '" . $allianceId . "';"
        );
    }

    public function updateAllianceOwner(int $allianceId, int $userRank): void
    {
        $this->db->query(
            'UPDATE `' . ALLIANCE . '` SET
                `alliance_owner` =
                (
                    SELECT `id`
                    FROM `' . USERS . "`
                    WHERE `ally_rank_id` = '" . $userRank . "'
                        AND `ally_id` = '" . $allianceId . "'
                    LIMIT 1
                )
            WHERE `alliance_id` = '" . $allianceId . "';"
        );
    }

    /**
     * Delete alliance
     *
     * @param Int $allianceId Alliance ID
     *
     * @return void
     */
    public function deleteAllianceById($allianceId)
    {
        $this->db->query(
            'DELETE ass, a FROM ' . ALLIANCE . ' AS a
            INNER JOIN ' . ALLIANCE_STATISTICS . " AS ass ON ass.alliance_statistic_alliance_id = a.alliance_id
            WHERE a.`alliance_id` = '" . $allianceId . "';"
        );

        $this->db->query(
            'UPDATE `' . USERS . "` SET
                `ally_id` = '0',
                `ally_request` = '0',
                `ally_request_text` = '',
                `ally_register_time` = '',
                `ally_rank_id` = '0'
            WHERE `ally_id` = '" . $allianceId . "';"
        );
    }

    public function deletePlanetsAndRelatedDataByUserId(int $userId): void
    {
        $this->db->query(
            'DELETE p,b,d,s FROM ' . PLANETS . ' AS p
            INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id`
            INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
            INNER JOIN ' . SHIPS . " AS s ON s.ship_planet_id = p.`planet_id`
            WHERE `planet_user_id` = '" . $userId . "';"
        );
    }

    public function deleteMessagesByUserId(int $userId): void
    {
        $this->db->query(
            'DELETE FROM ' . MESSAGES . "
                WHERE `message_sender` = '" . $userId . "' OR `message_receiver` = '" . $userId . "';"
        );
    }

    public function deleteBuddysByUserId(int $userId): void
    {
        $this->db->query(
            'DELETE FROM ' . BUDDY . "
                WHERE `buddy_sender` = '" . $userId . "' OR `buddy_receiver` = '" . $userId . "';"
        );
    }

    public function deleteUserDataById(int $userId): void
    {
        $this->db->query(
            'DELETE r,f,n,p,pr,s,u FROM ' . USERS . ' AS u
            INNER JOIN ' . RESEARCH . ' AS r ON r.research_user_id = u.id
            LEFT JOIN ' . FLEETS . ' AS f ON f.fleet_owner = u.id
            LEFT JOIN ' . NOTES . ' AS n ON n.note_owner = u.id
            INNER JOIN ' . PREMIUM . ' AS p ON p.premium_user_id = u.id
            INNER JOIN ' . PREFERENCES . ' AS pr ON pr.preference_user_id = u.id
            INNER JOIN ' . USERS_STATISTICS . " AS s ON s.user_statistic_user_id = u.id
            WHERE u.`id` = '" . $userId . "';"
        );
    }

    public function setUserDataByUserId(): array
    {
        if (!defined('IN_ADMIN')) {
            return $this->db->queryFetch(
                'SELECT
                    u.*,
                    pre.*,
                    pr.*,
                    usul.user_statistic_total_rank,
                    usul.user_statistic_total_points,
                    r.*,
                    a.alliance_name,
                    (
                        SELECT COUNT(`message_id`) AS `new_message`
                        FROM `' . MESSAGES . '`
                        WHERE `message_receiver` = u.`id` AND `message_read` = 0
                    ) AS `new_message`
                FROM `' . USERS . '` AS u
                INNER JOIN `' . PREFERENCES . '` AS pr ON pr.preference_user_id = u.id
                INNER JOIN `' . USERS_STATISTICS . '` AS usul ON usul.user_statistic_user_id = u.id
                INNER JOIN `' . PREMIUM . '` AS pre ON pre.premium_user_id = u.id
                INNER JOIN `' . RESEARCH . '` AS r ON r.research_user_id = u.id
                LEFT JOIN `' . ALLIANCE . "` AS a ON a.alliance_id = u.ally_id
                WHERE (u.`id` = '" . session('user_id') . "')
                LIMIT 1;"
            );
        }

        return $this->db->queryFetch(
            'SELECT
                u.*
            FROM `' . USERS . "` AS u
            WHERE (u.`id` = '" . session('user_id') . "')
            LIMIT 1;"
        );
    }

    public function updateUserActivityData(string $request_uri, string $remote_addr, string $agent, int $userId): void
    {
        $this->db->query(
            'UPDATE ' . USERS . " SET
                `onlinetime` = '" . time() . "',
                `current_page` = '" . $this->db->escapeValue($request_uri) . "',
                `lastip` = '" . $this->db->escapeValue($remote_addr) . "',
                `agent` = '" . $this->db->escapeValue($agent) . "'
            WHERE `id` = '" . $userId . "'
            LIMIT 1;"
        );
    }

    public function setPlanetData(int $planetId, int $adminLevel): array
    {
        return $this->db->queryFetch(
            'SELECT p.*, b.*, d.*, s.*,
            m.planet_id AS moon_id,
            m.planet_name AS moon_name,
            m.planet_image AS moon_image,
            m.planet_destroyed AS moon_destroyed,
            m.planet_image AS moon_image,
            (SELECT COUNT(user_statistic_user_id) AS stats_users
                FROM `' . USERS_STATISTICS . '` AS s
                INNER JOIN ' . USERS . ' AS u ON u.id = s.user_statistic_user_id
                WHERE u.`authlevel` <= ' . $adminLevel . ') AS stats_users
            FROM ' . PLANETS . ' AS p
            INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id`
            INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
            INNER JOIN ' . SHIPS . ' AS s ON s.ship_planet_id = p.`planet_id`
            LEFT JOIN ' . PLANETS . ' AS m ON m.planet_id = (SELECT mp.`planet_id`
                FROM ' . PLANETS . " AS mp
                WHERE (mp.planet_galaxy=p.planet_galaxy AND
                                mp.planet_system=p.planet_system AND
                                mp.planet_planet=p.planet_planet AND
                                mp.planet_type=3))
            WHERE p.`planet_id` = '" . $planetId . "';"
        );
    }

    public function getUserPlanetByIdAndUserId(int $planetId, int $userId): ?array
    {
        return $this->db->queryFetch(
            'SELECT `planet_id`
            FROM ' . PLANETS . "
            WHERE `planet_id` = '" . $planetId . "'
            AND `planet_user_id` = '" . $userId . "'
            AND `planet_destroyed` = 0;"
        );
    }

    public function changeUserPlanetByUserId(int $planetId, int $userId): void
    {
        $this->db->query(
            'UPDATE ' . USERS . " SET
            `current_planet` = '" . $planetId . "'
            WHERE `id` = '" . $userId . "';"
        );
    }

    public function createNewUser(string $insertQuery): int
    {
        $this->db->query($insertQuery);

        return $this->db->insertId();
    }

    public function createPremium(int $userId): void
    {
        $this->db->query(
            'INSERT INTO `' . PREMIUM . "` (`premium_user_id`, `premium_dark_matter`)
            VALUES('" . $userId . "', '" . Options::getInstance()->get('registration_dark_matter') . "');"
        );
    }

    public function createResearch(int $userId): void
    {
        $this->db->query(
            'INSERT INTO ' . RESEARCH . " SET `research_user_id` = '" . $userId . "';"
        );
    }

    public function createSettings(int $userId): void
    {
        $this->db->query(
            'INSERT INTO ' . PREFERENCES . " SET `preference_user_id` = '" . $userId . "';"
        );
    }

    public function createUserStatistics(int $userId): void
    {
        $this->db->query(
            'INSERT INTO ' . USERS_STATISTICS . " SET `user_statistic_user_id` = '" . $userId . "';"
        );
    }
}
