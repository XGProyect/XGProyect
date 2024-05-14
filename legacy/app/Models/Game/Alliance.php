<?php

namespace Xgp\App\Models\Game;

use Exception;
use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Alliance extends Model
{
    public function getAllianceDataById(?int $alliance_id): array
    {
        $result[] = $this->db->queryFetch(
            'SELECT a.*,
                    (SELECT COUNT(id) AS `alliance_members`
                        FROM `' . USERS . '`
                        WHERE `ally_id` = a.`alliance_id`) AS `alliance_members`
            FROM `' . ALLIANCE . "` AS a
            WHERE a.`alliance_id` = '" . (int) $alliance_id . "'
            LIMIT 1;"
        );

        return $result;
    }

    /**
     * Create a new alliance with the provided params
     *
     * @param string $alliance_name Alliance Name
     * @param string $alliance_tag  Alliance Tag
     * @param int $userId          User ID
     * @param string $founder_rank  Founder Rank
     * @param string $newcomer_rank  New member Rank
     *
     * @return void
     */
    public function createNewAlliance($alliance_name, $alliance_tag, $userId, $founder_rank, $newcomer_rank)
    {
        try {
            $this->db->beginTransaction();

            $rights_string = '[{"rank":"Founder","rights":{"1":1,"2":1,"3":1,"4":1,"5":1,"6":1,"7":1,"8":1,"9":1}},{"rank":"Newcomer","rights":{"1":0,"2":0,"3":0,"4":0,"5":0,"6":0,"7":0,"8":0,"9":0}}]';

            $this->db->query(
                'INSERT INTO `' . ALLIANCE . "` SET
                `alliance_name` = '" . $alliance_name . "',
                `alliance_tag` = '" . $alliance_tag . "' ,
                `alliance_owner` = '" . (int) $userId . "',
                `alliance_register_time` = '" . time() . "',
                `alliance_ranks` = '" . strtr($rights_string, ['Founder' => $founder_rank, 'Newcomer' => $newcomer_rank]) . "'"
            );

            $new_ally_id = $this->db->insertId();

            $this->db->query(
                'INSERT INTO ' . ALLIANCE_STATISTICS . " SET
                `alliance_statistic_alliance_id`='" . $new_ally_id . "'"
            );

            $this->db->query(
                'UPDATE ' . USERS . " SET
                `ally_id`='" . $new_ally_id . "',
                `ally_register_time`='" . time() . "'
                WHERE `id`='" . (int) $userId . "'"
            );

            $this->db->commitTransaction();
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
        }
    }

    /**
     * Search an alliance by name or tag
     *
     * @param string $name_tag Name or Tag
     *
     * @return array
     */
    public function searchAllianceByNameTag($name_tag)
    {
        return $this->db->queryFetchAll(
            'SELECT a.alliance_id,
                    a.alliance_tag,
                    a.alliance_name,
                (SELECT COUNT(id) AS `alliance_members`
                    FROM `' . USERS . '`
                    WHERE `ally_id` = a.`alliance_id`) AS `alliance_members`
            FROM ' . ALLIANCE . " AS a
            WHERE a.alliance_name LIKE '%" . $this->db->escapeValue($name_tag) . "%' OR
                    a.alliance_tag LIKE '%" . $this->db->escapeValue($name_tag) . "%' LIMIT 30"
        );
    }

    /**
     * Update users table to set the alliance request
     *
     * @param int    $alliance_id  Alliance ID
     * @param string $text Request Text
     * @param int    $userId      User ID
     *
     * @retun void
     */
    public function createNewUserRequest($alliance_id, $text, $userId)
    {
        $this->db->query(
            'UPDATE `' . USERS . "` SET
            `ally_request` = '" . (int) $alliance_id . "' ,
            `ally_request_text` = '" . $text . "',
            `ally_register_time` = '" . time() . "',
            `ally_rank_id` = '1'
            WHERE `id`='" . (int) $userId . "'"
        );
    }

    public function cancelUserRequestById(int $userId): void
    {
        $this->db->query(
            'UPDATE ' . USERS . "
                SET `ally_request` = '0'
            WHERE `id`= '" . (int) $userId . "'"
        );
    }

    public function exitAlliance($alliance_id, int $userId): void
    {
        $this->db->query(
            'UPDATE `' . USERS . "` SET
                `ally_id` = '0',
                `ally_rank_id` = '0'
            WHERE `id` = '" . (int) $userId . "'
                AND `ally_id` = '" . (int) $alliance_id . "'"
        );
    }

    public function getAllianceRequestsCount(int $alliance_id): array
    {
        return $this->db->queryFetch(
            'SELECT COUNT(id) AS total_requests
                FROM `' . USERS . "`
                WHERE `ally_request` = '" . $alliance_id . "'"
        );
    }

    public function getAllianceMembers(int $alliance_id, $sort_by_field, $sort_by_order): array
    {
        return $this->db->queryFetchAll(
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
        );

        return [];
    }

    /**
     * Get alliance members filtered by alliance ID
     *
     * @param int $alliance_id Alliance ID
     *
     * @return array
     */
    public function getAllianceMembersById($alliance_id)
    {
        return $this->db->queryFetchAll(
            'SELECT `id`, `name`, `ally_rank_id`
                FROM `' . USERS . "`
                WHERE `ally_id` = '" . (int) $alliance_id . "'"
        );
    }

    /**
     * Get alliance members filtered by alliance ID and Rank ID
     *
     * @param int $alliance_id Alliance ID
     * @param int $rank_id     Rank ID
     *
     * @return array
     */
    public function getAllianceMembersByIdAndRankId($alliance_id, $rank_id)
    {
        return $this->db->queryFetchAll(
            'SELECT `id`, `name`
            FROM `' . USERS . "`
            WHERE `ally_id` = '" . (int) $alliance_id . "' AND
                `ally_rank_id` = '" . (int) $rank_id . "'"
        );
    }

    /**
     * Update alliance ranks
     *
     * @param int    $alliance_id Alliance ID
     * @param string $ranks       Ranks
     */
    public function updateAllianceRanks($alliance_id, $ranks)
    {
        $this->db->query(
            'UPDATE `' . ALLIANCE . "` SET
                `alliance_ranks` = '" . $ranks . "'
            WHERE `alliance_id` = '" . (int) $alliance_id . "'"
        );
    }

    /**
     * Update alliance settings
     *
     * @param int $alliance_id     Alliance ID
     * @param array $alliance_data Alliance Data
     *
     * @return void
     */
    public function updateAllianceSettings($alliance_id, $alliance_data)
    {
        $this->db->query(
            'UPDATE `' . ALLIANCE . "` SET
                `alliance_image` = '" . $alliance_data['alliance_image'] . "',
                `alliance_web` = '" . $alliance_data['alliance_web'] . "',
                `alliance_request_notallow` = '" . $alliance_data['alliance_request_notallow'] . "'
            WHERE `alliance_id` = '" . $alliance_id . "'"
        );
    }

    /**
     *
     * @param int    $alliance_id Alliance ID
     * @param string $text        Text
     *
     * @return void
     */
    public function updateAllianceRequestText($alliance_id, $text)
    {
        $this->db->query(
            'UPDATE ' . ALLIANCE . " SET
                `alliance_request`='" . $text . "'
            WHERE `alliance_id` = '" . (int) $alliance_id . "'"
        );
    }

    /**
     *
     * @param int    $alliance_id Alliance ID
     * @param string $text        Text
     *
     * @return void
     */
    public function updateAllianceText($alliance_id, $text)
    {
        $this->db->query(
            'UPDATE ' . ALLIANCE . " SET
                `alliance_text`='" . $text . "'
            WHERE `alliance_id` = '" . (int) $alliance_id . "'"
        );
    }

    /**
     *
     * @param int    $alliance_id Alliance ID
     * @param string $text        Text
     *
     * @return void
     */
    public function updateAllianceDescription($alliance_id, $text)
    {
        $this->db->query(
            'UPDATE ' . ALLIANCE . " SET
                `alliance_description`='" . $text . "'
            WHERE `alliance_id` = '" . (int) $alliance_id . "'"
        );
    }

    /**
     *
     * @param int    $userId User ID
     * @param string $rank    Rank
     */
    public function updateUserRank($userId, $rank)
    {
        $this->db->query(
            'UPDATE ' . USERS . " SET
                `ally_rank_id` = '" . $this->db->escapeValue($rank) . "'
            WHERE `id`='" . (int) $userId . "'"
        );
    }

    /**
     * Add an user to the alliance
     *
     * @param int $userId     User ID
     * @param int $alliance_id Alliance ID
     *
     * @return void
     */
    public function addUserToAlliance($userId, $alliance_id)
    {
        $this->db->query(
            'UPDATE `' . USERS . "` SET
                `ally_request_text` = '',
                `ally_request` = '0',
                `ally_id` = '" . (int) $alliance_id . "'
            WHERE `id` = '" . (int) $userId . "'"
        );
    }

    public function removeUserFromAlliance(int $userId): void
    {
        $this->addUserToAlliance($userId, 0);
    }

    public function getAllianceRequests(int $alliance_id): array
    {
        return $this->db->queryFetchAll(
            'SELECT `id`,
                    `name`,
                    `ally_request_text`,
                    `ally_register_time`
            FROM `' . USERS . "`
            WHERE `ally_request` = '" . $alliance_id . "'"
        );
    }

    /**
     *
     * @param int    $alliance_id Alliance ID
     * @param string $alliance_name Alliance Name
     */
    public function updateAllianceName($alliance_id, $alliance_name)
    {
        $this->db->query(
            'UPDATE ' . ALLIANCE . " AS a SET
                a.`alliance_name` = '" . $alliance_name . "'
            WHERE a.`alliance_id` = '" . $alliance_id . "';"
        );
    }

    /**
     *
     * @param int    $alliance_id  Alliance ID
     * @param string $alliance_tag Alliance Tag
     */
    public function updateAllianceTag($alliance_id, $alliance_tag)
    {
        $this->db->query(
            'UPDATE ' . ALLIANCE . " SET
                `alliance_tag` = '" . $alliance_tag . "'
            WHERE `alliance_id` = '" . $alliance_id . "';"
        );
    }

    public function deleteAlliance(int $alliance_id): void
    {
        try {
            $this->db->beginTransaction();

            $this->db->query(
                'UPDATE `' . USERS . "` SET
                    `ally_id` = '0',
                    `ally_rank_id` = '0'
                WHERE `ally_id` = '" . $alliance_id . "'"
            );

            $this->db->query(
                'DELETE FROM `' . ALLIANCE . "`
                WHERE `alliance_id` = '" . $alliance_id . "'
                LIMIT 1"
            );

            $this->db->query(
                'DELETE FROM `' . ALLIANCE_STATISTICS . "`
                WHERE `alliance_statistic_alliance_id` = '" . $alliance_id . "'
                LIMIT 1"
            );

            $this->db->commitTransaction();
        } catch (Exception $e) {
            $this->db->rollbackTransaction();
        }
    }

    /**
     *
     * @param int $alliance_id     Alliance ID
     * @param int $current_user_id Current User ID
     * @param int $new_leader      New Leader ID
     *
     * @return void
     */
    public function transferAlliance($alliance_id, $current_user_id, $new_leader)
    {
        $this->db->query(
            'UPDATE `' . USERS . '` AS u1, `' . ALLIANCE . '` AS a, `' . USERS . "` AS u2 SET
                u1.`ally_rank_id` = '1',
                a.`alliance_owner` = '" . (int) $new_leader . "',
                u2.`ally_rank_id` = '0'
            WHERE u1.`id` = " . $current_user_id . ' AND
                a.`alliance_id` = ' . $alliance_id . " AND
                u2.`id` = '" . (int) $new_leader . "'"
        );
    }

    public function checkAllianceName(string $alliance_name): ?string
    {
        return $this->db->queryFetch(
            'SELECT `alliance_name`
            FROM `' . ALLIANCE . "`
            WHERE `alliance_name` = '" . $this->db->escapeValue($alliance_name) . "'"
        );
    }

    public function checkAllianceTag(string $alliance_tag): ?string
    {
        return $this->db->queryFetch(
            'SELECT `alliance_tag`
            FROM `' . ALLIANCE . "`
            WHERE `alliance_tag` = '" . $this->db->escapeValue($alliance_tag) . "'"
        );
    }

    /**
     * Return the sort method
     *
     * @param int $sort_field Sort by field
     * @param int $sort_order Sort by order [ASC|DESC]
     *
     * @return string
     */
    private function returnSort($sort_field, $sort_order)
    {
        // FIRST ORDER
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

        // SECOND ORDER
        if ($sort_order == 1) {
            $sort .= ' DESC;';
        } elseif ($sort_order == 2) {
            $sort .= ' ASC;';
        }

        return $sort;
    }
}
