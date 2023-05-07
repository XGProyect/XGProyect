<?php

namespace Xgp\App\Models\Game;

use Xgp\App\Core\Model;

class Messages extends Model
{
    /**
     * Get the list of messages by user id and type
     *
     * @param int    $userId         User id
     * @param string $msg_type_string Message types
     *
     * @return mixed
     */
    public function getByUserIdAndType($userId, $msg_type_string)
    {
        if ((int) $userId > 0 && !empty($msg_type_string)) {
            return $this->db->queryFetchAll(
                'SELECT *
                FROM `' . MESSAGES . '`
                WHERE `message_receiver` = ' . $userId . '
                        AND `message_type` IN (' . rtrim($msg_type_string, ',') . ')
                ORDER BY `message_time` DESC;'
            );
        }

        return null;
    }

    public function getByUserId(int $userId): ?array
    {
        if ($userId > 0) {
            return $this->db->queryFetchAll(
                'SELECT
                    *
                FROM `' . MESSAGES . "`
                WHERE `message_receiver` = '" . $userId . "'
                ORDER BY `message_time` DESC;"
            );
        }

        return null;
    }

    /**
     * Mark messages as read by user id and type
     *
     * @param int    $userId         User id
     * @param string $msg_type_string Message types
     *
     * @return mixed
     */
    public function markAsReadByType($userId, $msg_type_string)
    {
        if ((int) $userId > 0 && !empty($msg_type_string)) {
            return $this->db->query(
                'UPDATE `' . MESSAGES . "` SET
                    `message_read` = '1'
                WHERE `message_receiver` = " . $userId . '
                        AND `message_type` IN (' . rtrim($msg_type_string, ',') . ');'
            );
        }

        return null;
    }

    public function markAsRead(int $userId): void
    {
        if ($userId > 0) {
            $this->db->query(
                'UPDATE `' . MESSAGES . "` SET
                    `message_read` = '1'
                WHERE `message_receiver` = " . $userId . ';'
            );
        }
    }

    public function getHomePlanet(int $planetId): ?array
    {
        if ((int) $planetId > 0) {
            return $this->db->queryFetch(
                'SELECT u.`user_id`, u.`user_name`, p.`planet_galaxy`, p.`planet_system`, p.`planet_planet`
                FROM ' . PLANETS . ' AS p
                INNER JOIN ' . USERS . " as u ON p.planet_user_id = u.user_id
                WHERE p.`planet_user_id` = '" . $planetId . "';"
            );
        }

        return null;
    }

    public function deleteAllByOwner(int $userId): void
    {
        if ($userId > 0) {
            $this->db->query(
                'DELETE FROM ' . MESSAGES . "
                WHERE `message_receiver` = '" . $userId . "';"
            );
        }
    }

    /**
     * Delete message by id and current user
     *
     * @param int $userId       The user ID
     * @param int $messages_ids  The messages ID
     *
     * @return mixed
     */
    public function deleteByOwnerAndIds(int $userId, $messages_ids)
    {
        if ($userId > 0) {
            return $this->db->query(
                'DELETE FROM ' . MESSAGES . '
                WHERE `message_id` IN (' . $messages_ids . ")
                    AND `message_receiver` = '" . $userId . "';"
            );
        }

        return null;
    }

    /**
     * Delete message by id and current user
     *
     * @param int $userId       The user ID
     * @param int $message_type  The messages type
     *
     * @return mixed
     */
    public function deleteByOwnerAndMessageType($userId, $message_type)
    {
        if ((int) $userId > 0 && (int) $message_type >= 0) {
            return $this->db->query(
                'DELETE FROM ' . MESSAGES . '
                WHERE `message_type` IN (' . $message_type . ")
                    AND `message_receiver` = '" . $userId . "';"
            );
        }

        return null;
    }

    public function countMessagesByType(int $userId): array
    {
        if ($userId > 0) {
            return $this->db->queryFetchAll(
                'SELECT
                    `message_type`,
                    COUNT(`message_type`) AS message_type_count,
                    SUM(`message_read` = 0) AS unread_count
                FROM `' . MESSAGES . "`
                WHERE `message_receiver` = '" . $userId . "'
                GROUP BY `message_type`"
            );
        }

        return null;
    }

    /**
     * Count alliance members, buddys, operators and notes
     *
     * @param int $userId      User ID
     * @param int $user_ally_id User Alliance ID
     *
     * @return mixed
     */
    public function countAddressBookAndNotes($userId, $user_ally_id)
    {
        if ((int) $userId > 0 && (int) $user_ally_id >= 0) {
            return $this->db->queryFetch(
                'SELECT
                ( SELECT COUNT(`user_id`)
                    FROM `' . USERS . "`
                    WHERE `user_ally_id` = '" . $user_ally_id . "'
                        AND `user_ally_id` <> 0
                        AND `user_id` <> '" . $userId . "'
                    ) AS alliance_count,

                    ( SELECT COUNT(`buddy_id`)
                    FROM `" . BUDDY . "`
                    WHERE `buddy_sender` = '" . $userId . "'
                        OR `buddy_receiver` = '" . $userId . "'
                    ) AS buddys_count,

                    ( SELECT COUNT(`note_id`)
                    FROM `" . NOTES . "`
                    WHERE `note_owner` = '" . $userId . "'
                    ) AS notes_count,

                    ( SELECT COUNT(`user_id`)
                    FROM " . USERS . "
                    WHERE user_authlevel <> 0
                        AND `user_id` <> '" . $userId . "'
                    ) AS operators_count"
            );
        }

        return null;
    }

    public function getFriends(int $userId): ?array
    {
        if ($userId > 0) {
            return $this->db->queryFetchAll(
                'SELECT
                    u.`user_id`,
                    u.`user_name`,
                    u.`user_email`
                FROM `' . BUDDY . '` b
                LEFT JOIN `' . USERS . "` u
                    ON u.user_id = IF(`buddy_sender` = '" . $userId . "', `buddy_receiver`, `buddy_sender`)
                WHERE `buddy_sender`='" . $userId . "'
                    OR `buddy_receiver`='" . $userId . "'"
            );
        }

        return null;
    }

    /**
     * Get all alliance members that the user can contact
     *
     * @param int $userId      User ID
     * @param int $user_ally_id User Alliance ID
     *
     * @return mixed
     */
    public function getAllianceMembers($userId, $user_ally_id)
    {
        if ((int) $userId > 0 && (int) $user_ally_id > 0) {
            return $this->db->query(
                'SELECT `user_id`, `user_name`, `user_email`
                FROM ' . USERS . "
                WHERE user_ally_id = '" . $user_ally_id . "'
                    AND `user_id` <> '" . $userId . "';"
            );
        }

        return null;
    }

    public function getOperators(int $userId): ?array
    {
        if ($userId > 0) {
            return $this->db->queryFetchAll(
                'SELECT `user_name`, `user_email`
                FROM ' . USERS . "
                WHERE user_authlevel > '0'
                    AND `user_id` <> '" . $userId . "';"
            );
        }

        return null;
    }

    public function getNotes(int $userId): ?array
    {
        if ($userId > 0) {
            return $this->db->queryFetchAll(
                'SELECT `note_id`, `note_priority`, `note_title`
                FROM `' . NOTES . "`
                WHERE `note_owner` = '" . $userId . "';"
            );
        }

        return null;
    }
}
