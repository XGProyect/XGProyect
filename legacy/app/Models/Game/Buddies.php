<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

/**
 * @deprecated v4.0.0 use laravel instead
 */
class Buddies extends Model
{
    /**
     * Get buddy data by ID
     *
     * @param int $buddy_id Buddy ID
     *
     * @return int $buddy_id Buddy ID
     */
    public function getBuddyDataByBuddyId($buddy_id)
    {
        return $this->db->queryFetch(
            'SELECT *
            FROM `' . BUDDY . "`
            WHERE `buddy_id` = '" . (int) $buddy_id . "'"
        );
    }

    public function getBuddiesByUserId(int $userId): array
    {
        return $this->db->queryFetchAll(
            'SELECT *
                FROM `' . BUDDY . "`
                WHERE `buddy_sender` = '" . (int) $userId . "'
                    OR `buddy_receiver` = '" . (int) $userId . "'"
        );
    }

    public function getBuddyDataById(int $userId): ?array
    {
        return $this->db->queryFetch(
            'SELECT u.`id`,
                    u.`name`,
                    u.`galaxy`,
                    u.`system`,
                    u.`planet`,
                    u.`onlinetime`,
                    a.`alliance_id`,
                    a.`alliance_name`
            FROM ' . USERS . ' AS u
            LEFT JOIN `' . ALLIANCE . "` AS a ON a.`alliance_id` = u.`ally_id`
            WHERE u.`id` = '" . $userId . "'"
        );
    }

    /**
     * Remove a buddy
     *
     * @param int $buddy_id Buddy Id
     * @param int $userId  Current User Id
     *
     * @return void
     */
    public function removeBuddyById($buddy_id, $userId)
    {
        $this->db->query(
            'DELETE FROM `' . BUDDY . "`
            WHERE `buddy_id` = '" . (int) $buddy_id . "'
                AND (`buddy_receiver` = '" . (int) $userId . "'
                        OR `buddy_sender` = '" . (int) $userId . "') "
        );
    }

    /**
     * Confirm player as a current user buddy
     *
     * @param int $buddy_id Buddy Id
     * @param int $userId  Current User Id
     *
     * @return void
     */
    public function setBuddyStatusById($buddy_id, $userId)
    {
        $this->db->query(
            'UPDATE `' . BUDDY . "`
                SET `buddy_status` = '1'
            WHERE `buddy_id` = '" . (int) $buddy_id . "' AND
                    `buddy_receiver` = '" . (int) $userId . "'"
        );
    }

    public function getBuddyIdByReceiverAndSender(int $sendTo, int $userId): ?array
    {
        return $this->db->queryFetch(
            'SELECT
                `buddy_id`
            FROM
                `' . BUDDY . "`
            WHERE (
                `buddy_receiver` = '" . $userId . "'
            AND
                `buddy_sender` = '" . $sendTo . "'
            ) OR (
                `buddy_receiver` = '" . $sendTo . "'
            AND
                `buddy_sender` = '" . $userId . "'
            )"
        );
    }

    public function insertNewBuddyRequest(int $user, int $userId, string $text): void
    {
        $this->db->query(
            'INSERT INTO `' . BUDDY . "` SET
                `buddy_sender` = '" . (int) $userId . "',
                `buddy_receiver` = '" . (int) $user . "',
                `buddy_status` = '0',
                `buddy_request_text` = '" . $this->db->escapeValue(strip_tags($text)) . "'"
        );
    }

    public function checkIfBuddyExists(int $userId): array
    {
        return $this->db->queryFetch(
            'SELECT
                `id`,
                `name`
            FROM `' . USERS . "`
            WHERE `id` = '" . $userId . "'"
        );
    }

    public function getBuddiesDetailsForAcsById(int $userId, int $group_id): array
    {
        return $this->db->queryFetchAll(
            'SELECT DISTINCT
                u.`id`,
                u.`name`
            FROM `' . BUDDY . '` AS b
            LEFT JOIN `' . USERS . "` AS u
            	ON ((u.id = b.buddy_sender) OR (u.id = b.buddy_receiver))
            WHERE
            (
                b.`buddy_sender` = '" . $userId . "'
            OR
                b.`buddy_receiver` = '" . $userId . "'
            )
            AND b.`buddy_status` = '1'
            AND u.`id` NOT IN (
                SELECT
                    acs.`acs_user_id`
                FROM `" . ACS_MEMBERS . "` acs
                WHERE acs.`acs_group_id` = '" . $group_id . "'
            )"
        ) ?? [];
    }
}
