<?php

declare(strict_types=1);

namespace Xgp\App\Models\Libraries;

use App\Services\SettingsService;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;

/**
 * @deprecated v4.0.0 use laravel instead
 *
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class UsersLibrary
{
    use PreparesLegacySql;

    public function getAllyIdByUserId(int $userId): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT `ally_id` FROM `' . USERS . "` WHERE `id` = '" . $userId . "';"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getAllianceDataByAllianceId(int $allianceId): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT a.`alliance_id`, a.`alliance_ranks`,
                    (SELECT COUNT(id) AS `ally_members`
                        FROM `' . USERS . "`
                        WHERE `ally_id` = '" . $allianceId . "') AS `ally_members`
                FROM `" . ALLIANCE . "` AS a
                WHERE a.`alliance_id` = '" . $allianceId . "';"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function updateAllianceOwner(int $allianceId, int $userRank): void
    {
        DB::statement(
            $this->prepareSql(
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
            )
        );
    }

    public function deleteAllianceById($allianceId)
    {
        DB::statement(
            $this->prepareSql(
                'DELETE ass, a FROM ' . ALLIANCE . ' AS a
                INNER JOIN ' . ALLIANCE_STATISTICS . " AS ass ON ass.alliance_statistic_alliance_id = a.alliance_id
                WHERE a.`alliance_id` = '" . $allianceId . "';"
            )
        );

        DB::statement(
            $this->prepareSql(
                'UPDATE `' . USERS . "` SET
                    `ally_id` = '0',
                    `ally_request` = '0',
                    `ally_request_text` = '',
                    `ally_register_time` = '',
                    `ally_rank_id` = '0'
                WHERE `ally_id` = '" . $allianceId . "';"
            )
        );
    }

    public function deletePlanetsAndRelatedDataByUserId(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE p,b,d,s FROM ' . PLANETS . ' AS p
                INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id`
                INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
                INNER JOIN ' . SHIPS . " AS s ON s.ship_planet_id = p.`planet_id`
                WHERE `planet_user_id` = '" . $userId . "';"
            )
        );
    }

    public function deleteMessagesByUserId(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE FROM ' . MESSAGES . "
                WHERE `message_sender` = '" . $userId . "' OR `message_receiver` = '" . $userId . "';"
            )
        );
    }

    public function deleteBuddysByUserId(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE FROM ' . BUDDY . "
                WHERE `buddy_sender` = '" . $userId . "' OR `buddy_receiver` = '" . $userId . "';"
            )
        );
    }

    public function deleteUserDataById(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'DELETE r,f,n,p,pr,s,u FROM ' . USERS . ' AS u
                INNER JOIN ' . RESEARCH . ' AS r ON r.research_user_id = u.id
                LEFT JOIN ' . FLEETS . ' AS f ON f.fleet_owner = u.id
                LEFT JOIN ' . NOTES . ' AS n ON n.note_owner = u.id
                INNER JOIN ' . PREMIUM . ' AS p ON p.premium_user_id = u.id
                INNER JOIN ' . PREFERENCES . ' AS pr ON pr.preference_user_id = u.id
                INNER JOIN ' . USERS_STATISTICS . " AS s ON s.user_statistic_user_id = u.id
                WHERE u.`id` = '" . $userId . "';"
            )
        );
    }

    public function setUserDataByUserId(): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
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
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function updateUserActivityData(string $request_uri, string $remote_addr, string $agent, int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . USERS . " SET
                    `onlinetime` = '" . time() . "',
                    `current_page` = ?,
                    `lastip` = ?,
                    `agent` = ?
                WHERE `id` = '" . $userId . "'
                LIMIT 1;"
            ),
            [$request_uri, $remote_addr, $agent]
        );
    }

    public function setPlanetData(int $planetId, int $adminLevel): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
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
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getUserPlanetByIdAndUserId(int $planetId, int $userId): ?array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT `planet_id`
                FROM ' . PLANETS . "
                WHERE `planet_id` = '" . $planetId . "'
                AND `planet_user_id` = '" . $userId . "'
                AND `planet_destroyed` = 0;"
            )
        );

        return $row !== null ? (array) $row : null;
    }

    public function changeUserPlanetByUserId(int $planetId, int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . USERS . " SET
                `current_planet` = '" . $planetId . "'
                WHERE `id` = '" . $userId . "';"
            )
        );
    }

    public function createNewUser(string $insertQuery): int
    {
        DB::statement($this->prepareSql($insertQuery));

        return (int) DB::getPdo()->lastInsertId();
    }

    public function createPremium(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . PREMIUM . "` (`premium_user_id`, `premium_dark_matter`)
                VALUES('" . $userId . "', '" . app(SettingsService::class)->getInt('registration_dark_matter') . "');"
            )
        );
    }

    public function createResearch(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO ' . RESEARCH . " SET `research_user_id` = '" . $userId . "';"
            )
        );
    }

    public function createSettings(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO ' . PREFERENCES . " SET `preference_user_id` = '" . $userId . "';"
            )
        );
    }

    public function createUserStatistics(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO ' . USERS_STATISTICS . " SET `user_statistic_user_id` = '" . $userId . "';"
            )
        );
    }
}
