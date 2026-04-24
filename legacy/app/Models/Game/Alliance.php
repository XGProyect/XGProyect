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
class Alliance
{
    use PreparesLegacySql;

    public function getAllianceDataById(?int $alliance_id): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT a.*,
                    (SELECT COUNT(id) AS `alliance_members`
                        FROM `' . USERS . '`
                        WHERE `ally_id` = a.`alliance_id`) AS `alliance_members`
                FROM `' . ALLIANCE . "` AS a
                WHERE a.`alliance_id` = '" . (int) $alliance_id . "'
                LIMIT 1;"
            )
        );

        return [($row !== null ? (array) $row : [])];
    }

    public function createNewAlliance($alliance_name, $alliance_tag, $userId, $founder_rank, $newcomer_rank)
    {
        DB::transaction(function () use ($alliance_name, $alliance_tag, $userId, $founder_rank, $newcomer_rank): void {
            $rights_string = '[{"rank":"Founder","rights":{"1":1,"2":1,"3":1,"4":1,"5":1,"6":1,"7":1,"8":1,"9":1}},{"rank":"Newcomer","rights":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0}}]';

            DB::statement(
                $this->prepareSql(
                    'INSERT INTO `' . ALLIANCE . "` SET
                    `alliance_name` = '" . $alliance_name . "',
                    `alliance_tag` = '" . $alliance_tag . "' ,
                    `alliance_owner` = '" . (int) $userId . "',
                    `alliance_register_time` = '" . time() . "',
                    `alliance_ranks` = '" . strtr($rights_string, ['Founder' => $founder_rank, 'Newcomer' => $newcomer_rank]) . "'"
                )
            );

            $new_ally_id = (int) DB::getPdo()->lastInsertId();

            DB::statement(
                $this->prepareSql(
                    'INSERT INTO ' . ALLIANCE_STATISTICS . " SET
                    `alliance_statistic_alliance_id`='" . $new_ally_id . "'"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'UPDATE ' . USERS . " SET
                    `ally_id`='" . $new_ally_id . "',
                    `ally_register_time`='" . time() . "'
                    WHERE `id`='" . (int) $userId . "'"
                )
            );
        });
    }

    public function searchAllianceByNameTag($name_tag)
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT a.alliance_id,
                        a.alliance_tag,
                        a.alliance_name,
                    (SELECT COUNT(id) AS `alliance_members`
                        FROM `' . USERS . '`
                        WHERE `ally_id` = a.`alliance_id`) AS `alliance_members`
                    FROM ' . ALLIANCE . ' AS a
                    WHERE a.alliance_name LIKE ?
                        OR a.alliance_tag LIKE ? LIMIT 30'
                ),
                ['%' . $name_tag . '%', '%' . $name_tag . '%']
            )
        );
    }

    public function createNewUserRequest($alliance_id, $text, $userId)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . USERS . "` SET
                `ally_request` = '" . (int) $alliance_id . "' ,
                `ally_request_text` = '" . $text . "',
                `ally_register_time` = '" . time() . "',
                `ally_rank_id` = '1'
                WHERE `id`='" . (int) $userId . "'"
            )
        );
    }

    public function cancelUserRequestById(int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . USERS . "
                    SET `ally_request` = '0'
                WHERE `id`= '" . (int) $userId . "'"
            )
        );
    }

    public function exitAlliance($alliance_id, int $userId): void
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . USERS . "` SET
                    `ally_id` = '0',
                    `ally_rank_id` = '0'
                WHERE `id` = '" . (int) $userId . "'
                    AND `ally_id` = '" . (int) $alliance_id . "'"
            )
        );
    }

    public function getAllianceRequestsCount(int $alliance_id): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT COUNT(id) AS total_requests
                FROM `' . USERS . "`
                WHERE `ally_request` = '" . $alliance_id . "'"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getAllianceMembers(int $alliance_id, $sort_by_field, $sort_by_order): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT u.id,
                        u.onlinetime,
                        u.name,
                        u.galaxy,
                        u.system,
                        u.planet,
                        u.ally_register_time,
                        u.ally_rank_id,
                        s.user_statistic_total_points
                    FROM `' . USERS . '` AS u
                    INNER JOIN `' . USERS_STATISTICS . "`AS s ON u.id = s.user_statistic_user_id
                    WHERE u.ally_id='" . $alliance_id . "'" . $this->returnSort($sort_by_field, $sort_by_order)
                )
            )
        );
    }

    public function getAllianceMembersById($alliance_id)
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT `id`, `name`, `ally_rank_id`
                    FROM `' . USERS . "`
                    WHERE `ally_id` = '" . (int) $alliance_id . "'"
                )
            )
        );
    }

    public function getAllianceMembersByIdAndRankId($alliance_id, $rank_id)
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT `id`, `name`
                    FROM `' . USERS . "`
                    WHERE `ally_id` = '" . (int) $alliance_id . "'
                        AND `ally_rank_id` = '" . (int) $rank_id . "'"
                )
            )
        );
    }

    public function updateAllianceRanks($alliance_id, $ranks)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . ALLIANCE . "` SET
                    `alliance_ranks` = '" . $ranks . "'
                WHERE `alliance_id` = '" . (int) $alliance_id . "'"
            )
        );
    }

    public function updateAllianceSettings($alliance_id, $alliance_data)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . ALLIANCE . "` SET
                    `alliance_image` = '" . $alliance_data['alliance_image'] . "',
                    `alliance_web` = '" . $alliance_data['alliance_web'] . "',
                    `alliance_request_notallow` = '" . $alliance_data['alliance_request_notallow'] . "'
                WHERE `alliance_id` = '" . $alliance_id . "'"
            )
        );
    }

    public function updateAllianceRequestText($alliance_id, $text)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . ALLIANCE . " SET
                    `alliance_request`='" . $text . "'
                WHERE `alliance_id` = '" . (int) $alliance_id . "'"
            )
        );
    }

    public function updateAllianceText($alliance_id, $text)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . ALLIANCE . " SET
                    `alliance_text`='" . $text . "'
                WHERE `alliance_id` = '" . (int) $alliance_id . "'"
            )
        );
    }

    public function updateAllianceDescription($alliance_id, $text)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . ALLIANCE . " SET
                    `alliance_description`='" . $text . "'
                WHERE `alliance_id` = '" . (int) $alliance_id . "'"
            )
        );
    }

    public function updateUserRank($userId, $rank)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . USERS . ' SET
                    `ally_rank_id` = ?
                WHERE `id`=?'
            ),
            [$rank, (int) $userId]
        );
    }

    public function addUserToAlliance($userId, $alliance_id)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . USERS . "` SET
                    `ally_request_text` = '',
                    `ally_request` = '0',
                    `ally_id` = '" . (int) $alliance_id . "'
                WHERE `id` = '" . (int) $userId . "'"
            )
        );
    }

    public function removeUserFromAlliance(int $userId): void
    {
        $this->addUserToAlliance($userId, 0);
    }

    public function getAllianceRequests(int $alliance_id): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT `id`,
                        `name`,
                        `ally_request_text`,
                        `ally_register_time`
                    FROM `' . USERS . "`
                    WHERE `ally_request` = '" . $alliance_id . "'"
                )
            )
        );
    }

    public function updateAllianceName($alliance_id, $alliance_name)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . ALLIANCE . " AS a SET
                    a.`alliance_name` = '" . $alliance_name . "'
                WHERE a.`alliance_id` = '" . $alliance_id . "';"
            )
        );
    }

    public function updateAllianceTag($alliance_id, $alliance_tag)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE ' . ALLIANCE . " SET
                    `alliance_tag` = '" . $alliance_tag . "'
                WHERE `alliance_id` = '" . $alliance_id . "';"
            )
        );
    }

    public function deleteAlliance(int $alliance_id): void
    {
        DB::transaction(function () use ($alliance_id): void {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . USERS . "` SET
                        `ally_id` = '0',
                        `ally_rank_id` = '0'
                    WHERE `ally_id` = '" . $alliance_id . "'"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'DELETE FROM `' . ALLIANCE . "`
                    WHERE `alliance_id` = '" . $alliance_id . "'
                    LIMIT 1"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'DELETE FROM `' . ALLIANCE_STATISTICS . "`
                    WHERE `alliance_statistic_alliance_id` = '" . $alliance_id . "'
                    LIMIT 1"
                )
            );
        });
    }

    public function transferAlliance($alliance_id, $current_user_id, $new_leader)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . USERS . '` AS u1, `' . ALLIANCE . '` AS a, `' . USERS . "` AS u2 SET
                    u1.`ally_rank_id` = '1',
                    a.`alliance_owner` = '" . (int) $new_leader . "',
                    u2.`ally_rank_id` = '0'
                WHERE u1.`id` = " . $current_user_id . ' AND
                    a.`alliance_id` = ' . $alliance_id . " AND
                    u2.`id` = '" . (int) $new_leader . "'"
            )
        );
    }

    public function checkAllianceName(string $alliance_name): ?string
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT `alliance_name`
                FROM `' . ALLIANCE . '`
                WHERE `alliance_name` = ?'
            ),
            [$alliance_name]
        );

        return $row !== null ? $row->alliance_name : null;
    }

    public function checkAllianceTag(string $alliance_tag): ?string
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT `alliance_tag`
                FROM `' . ALLIANCE . '`
                WHERE `alliance_tag` = ?'
            ),
            [$alliance_tag]
        );

        return $row !== null ? $row->alliance_tag : null;
    }

    private function returnSort($sort_field, $sort_order)
    {
        switch ($sort_field) {
            case 1:
                $sort = ' ORDER BY `name`';
                break;
            case 2:
                $sort = ' ORDER BY `ally_rank_id`';
                break;
            case 3:
                $sort = ' ORDER BY `user_statistic_total_points`';
                break;
            case 4:
                $sort = ' ORDER BY `ally_register_time`';
                break;
            case 5:
                $sort = ' ORDER BY `onlinetime`';
                break;
            default:
                $sort = ' ORDER BY `id`';
                break;
        }

        if ($sort_order == 1) {
            $sort .= ' DESC;';
        } elseif ($sort_order == 2) {
            $sort .= ' ASC;';
        }

        return $sort;
    }
}
