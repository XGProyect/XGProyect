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
class Messages
{
    use PreparesLegacySql;

    public function getByUserIdAndType($userId, $msg_type_string)
    {
        if ((int) $userId > 0 && !empty($msg_type_string)) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT *
                        FROM `' . MESSAGES . '`
                        WHERE `message_receiver` = ' . $userId . '
                            AND `message_type` IN (' . rtrim($msg_type_string, ',') . ')
                        ORDER BY `message_time` DESC;'
                    )
                )
            );
        }

        return null;
    }

    public function getByUserId(int $userId): ?array
    {
        if ($userId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT *
                        FROM `' . MESSAGES . "`
                        WHERE `message_receiver` = '" . $userId . "'
                        ORDER BY `message_time` DESC;"
                    )
                )
            );
        }

        return null;
    }

    public function markAsReadByType($userId, $msg_type_string)
    {
        if ((int) $userId > 0 && !empty($msg_type_string)) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . MESSAGES . "` SET
                        `message_read` = '1'
                    WHERE `message_receiver` = " . $userId . '
                        AND `message_type` IN (' . rtrim($msg_type_string, ',') . ');'
                )
            );

            return true;
        }

        return null;
    }

    public function markAsRead(int $userId): void
    {
        if ($userId > 0) {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . MESSAGES . "` SET
                        `message_read` = '1'
                    WHERE `message_receiver` = " . $userId . ';'
                )
            );
        }
    }

    public function getHomePlanet(int $planetId): ?array
    {
        if ((int) $planetId > 0) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT u.`id`, u.`name`, p.`planet_galaxy`, p.`planet_system`, p.`planet_planet`
                    FROM ' . PLANETS . ' AS p
                    INNER JOIN ' . USERS . " as u ON p.planet_user_id = u.id
                    WHERE p.`planet_user_id` = '" . $planetId . "';"
                )
            );

            return $row !== null ? (array) $row : null;
        }

        return null;
    }

    public function deleteAllByOwner(int $userId): void
    {
        if ($userId > 0) {
            DB::statement(
                $this->prepareSql(
                    'DELETE FROM ' . MESSAGES . "
                    WHERE `message_receiver` = '" . $userId . "';"
                )
            );
        }
    }

    public function deleteByOwnerAndIds(int $userId, $messages_ids)
    {
        if ($userId > 0) {
            DB::statement(
                $this->prepareSql(
                    'DELETE FROM ' . MESSAGES . '
                    WHERE `message_id` IN (' . $messages_ids . ")
                        AND `message_receiver` = '" . $userId . "';"
                )
            );

            return true;
        }

        return null;
    }

    public function deleteByOwnerAndMessageType($userId, $message_type)
    {
        if ((int) $userId > 0 && (int) $message_type >= 0) {
            DB::statement(
                $this->prepareSql(
                    'DELETE FROM ' . MESSAGES . '
                    WHERE `message_type` IN (' . $message_type . ")
                        AND `message_receiver` = '" . $userId . "';"
                )
            );

            return true;
        }

        return null;
    }

    public function countMessagesByType(int $userId): array
    {
        if ($userId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT
                            `message_type`,
                            COUNT(`message_type`) AS message_type_count,
                            SUM(`message_read` = 0) AS unread_count
                        FROM `' . MESSAGES . "`
                        WHERE `message_receiver` = '" . $userId . "'
                        GROUP BY `message_type`"
                    )
                )
            );
        }

        return null;
    }

    public function countAddressBookAndNotes($userId, $ally_id)
    {
        if ((int) $userId > 0 && (int) $ally_id >= 0) {
            $row = DB::selectOne(
                $this->prepareSql(
                    'SELECT
                    ( SELECT COUNT(`id`)
                        FROM `' . USERS . "`
                        WHERE `ally_id` = '" . $ally_id . "'
                            AND `ally_id` <> 0
                            AND `id` <> '" . $userId . "'
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

                        ( SELECT COUNT(`id`)
                        FROM " . USERS . "
                        WHERE authlevel <> 0
                            AND `id` <> '" . $userId . "'
                        ) AS operators_count"
                )
            );

            return $row !== null ? (array) $row : null;
        }

        return null;
    }

    public function getFriends(int $userId): ?array
    {
        if ($userId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT u.`id`, u.`name`, u.`email`
                        FROM `' . BUDDY . '` b
                        LEFT JOIN `' . USERS . "` u
                            ON u.id = IF(`buddy_sender` = '" . $userId . "', `buddy_receiver`, `buddy_sender`)
                        WHERE `buddy_sender`='" . $userId . "'
                            OR `buddy_receiver`='" . $userId . "'"
                    )
                )
            );
        }

        return null;
    }

    public function getAllianceMembers($userId, $ally_id)
    {
        if ((int) $userId > 0 && (int) $ally_id > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT `id`, `name`, `email`
                        FROM ' . USERS . "
                        WHERE ally_id = '" . $ally_id . "'
                            AND `id` <> '" . $userId . "';"
                    )
                )
            );
        }

        return null;
    }

    public function getOperators(int $userId): ?array
    {
        if ($userId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT `name`, `email`
                        FROM ' . USERS . "
                        WHERE authlevel > '0'
                            AND `id` <> '" . $userId . "';"
                    )
                )
            );
        }

        return null;
    }

    public function getNotes(int $userId): ?array
    {
        if ($userId > 0) {
            return array_map(
                fn ($row) => (array) $row,
                DB::select(
                    $this->prepareSql(
                        'SELECT `note_id`, `note_priority`, `note_title`
                        FROM `' . NOTES . "`
                        WHERE `note_owner` = '" . $userId . "';"
                    )
                )
            );
        }

        return null;
    }
}
