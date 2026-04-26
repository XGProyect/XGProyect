<?php

declare(strict_types=1);

namespace Xgp\App\Libraries;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use App\Services\SettingsService;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Libraries\Alliance\Ranks;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class Users
{
    use PreparesLegacySql;

    private array $userData = [];
    private array $planetData = [];
    private static ?Users $instance = null;

    public function __construct()
    {
        if (self::isSessionSet()) {
            // Get user data and check it
            $this->setUserData();

            // Set the changed planet
            $this->setPlanet();

            // Get planet data and check it
            $this->setPlanetData();

            // Update resources, ships, defenses & technologies
            UpdatesLibrary::updatePlanetResources($this->userData, $this->planetData, time());

            // Update buildings queue
            UpdatesLibrary::updateBuildingsQueue($this->planetData, $this->userData);

            // Update research queue
            UpdatesLibrary::updateResearchQueue($this->planetData, $this->userData);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Users();
        }

        return self::$instance;
    }

    public function getUserData(): array
    {
        return $this->userData;
    }

    public function getPlanetData(): array
    {
        return $this->planetData;
    }

    public function deleteUser(int $userId): void
    {
        $userRow = DB::selectOne($this->prepareSql('SELECT `ally_id` FROM `' . USERS . "` WHERE `id` = '" . $userId . "';"));
        $userData = $userRow !== null ? (array) $userRow : [];

        if ($userData['ally_id'] != 0) {
            $allianceRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT a.`alliance_id`, a.`alliance_ranks`,
                        (SELECT COUNT(id) AS `ally_members`
                            FROM `' . USERS . "`
                            WHERE `ally_id` = '" . (int) $userData['ally_id'] . "') AS `ally_members`
                    FROM `" . ALLIANCE . "` AS a
                    WHERE a.`alliance_id` = '" . (int) $userData['ally_id'] . "';"
                )
            );
            $alliance = $allianceRow !== null ? (array) $allianceRow : [];

            if ($alliance['ally_members'] > 1 && (isset($alliance['alliance_ranks']) && !is_null($alliance['alliance_ranks']))) {
                $ranks = new Ranks($alliance['alliance_ranks']);
                $userRank = null;

                // search for an user that has permission to receive the alliance.
                foreach ($ranks->getAllRanksAsArray() as $id => $rank) {
                    if (isset($rank['rights'][AllianceRanks::RIGHT_HAND]) && $rank['rights'][AllianceRanks::RIGHT_HAND] == SwitchInt::on) {
                        $userRank = $id;
                        break;
                    }
                }

                // check and update
                if (is_numeric($userRank)) {
                    DB::statement(
                        $this->prepareSql(
                            'UPDATE `' . ALLIANCE . '` SET
                                `alliance_owner` =
                                (
                                    SELECT `id`
                                    FROM `' . USERS . "`
                                    WHERE `ally_rank_id` = '" . $userRank . "'
                                        AND `ally_id` = '" . (int) $alliance['alliance_id'] . "'
                                    LIMIT 1
                                )
                            WHERE `alliance_id` = '" . (int) $alliance['alliance_id'] . "';"
                        )
                    );
                } else {
                    $this->deleteAllianceById((int) $alliance['alliance_id']);
                }
            } else {
                $this->deleteAllianceById((int) $alliance['alliance_id']);
            }
        }

        DB::statement(
            $this->prepareSql(
                'DELETE p,b,d,s FROM ' . PLANETS . ' AS p
                INNER JOIN ' . BUILDINGS . ' AS b ON b.building_planet_id = p.`planet_id`
                INNER JOIN ' . DEFENSES . ' AS d ON d.defense_planet_id = p.`planet_id`
                INNER JOIN ' . SHIPS . " AS s ON s.ship_planet_id = p.`planet_id`
                WHERE `planet_user_id` = '" . $userId . "';"
            )
        );
        DB::statement($this->prepareSql('DELETE FROM ' . MESSAGES . " WHERE `message_sender` = '" . $userId . "' OR `message_receiver` = '" . $userId . "';"));
        DB::statement($this->prepareSql('DELETE FROM ' . BUDDY . " WHERE `buddy_sender` = '" . $userId . "' OR `buddy_receiver` = '" . $userId . "';"));
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

    public function isOnVacations(array $user): bool
    {
        return ($user['preference_vacation_mode'] > 0);
    }

    public function isInactive(array $user): bool
    {
        return ($user['onlinetime'] < (time() - ONE_WEEK));
    }

    private static function isSessionSet(): bool
    {
        return session('user_id', false) && session('user_password', false);
    }

    private function deleteAllianceById(int $allianceId): void
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

    private function setUserData(): void
    {
        $userRow = DB::selectOne(
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
        $userRow = $userRow !== null ? (array) $userRow : [];

        $this->displayLoginErrors($userRow);

        // update user activity data
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . USERS . " SET
                    `onlinetime` = '" . time() . "',
                    `current_page` = ?,
                    `lastip` = ?,
                    `agent` = ?
                WHERE `id` = '" . (int) session('user_id') . "'
                LIMIT 1;"
            ),
            [$_SERVER['REQUEST_URI'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
        );

        // pass the data
        $this->userData = $userRow;

        // unset the old data
        unset($userRow);
    }

    private function displayLoginErrors(array $userRow): void
    {
        if ($userRow['id'] != session('user_id')) {
            Functions::redirect(SYSTEM_ROOT);
        }

        if (Auth::id() !== session('user_id')) {
            Functions::redirect(SYSTEM_ROOT);
        }

        if (!Hash::check(($userRow['password'] . '-' . config('SECRETWORD')), session('user_password'))) {
            Functions::redirect(SYSTEM_ROOT);
        }
    }

    private function setPlanetData(): void
    {
        $planetId = (int) $this->userData['current_planet'];
        $adminLevel = app(SettingsService::class)->getInt('stat_admin_level');

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

        $this->planetData = $row !== null ? (array) $row : [];
    }

    private function setPlanet(): void
    {
        $select = isset($_GET['cp']) ? (int) $_GET['cp'] : '';
        $restore = isset($_GET['re']) ? (int) $_GET['re'] : '';

        if (is_numeric($select) && $restore == 0 && $select != 0) {
            $ownedRow = DB::selectOne(
                $this->prepareSql(
                    'SELECT `planet_id`
                    FROM ' . PLANETS . "
                    WHERE `planet_id` = '" . $select . "'
                    AND `planet_user_id` = '" . (int) $this->userData['id'] . "'
                    AND `planet_destroyed` = 0;"
                )
            );

            if ($ownedRow !== null) {
                $this->userData['current_planet'] = $select;
                DB::statement(
                    $this->prepareSql(
                        'UPDATE ' . USERS . " SET
                        `current_planet` = '" . $select . "'
                        WHERE `id` = '" . (int) $this->userData['id'] . "';"
                    )
                );
            }
        }
    }
}
