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
class Buddies
{
    use PreparesLegacySql;

    public function getBuddyDataByBuddyId($buddy_id)
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT *
                FROM `' . BUDDY . "`
                WHERE `buddy_id` = '" . (int) $buddy_id . "'"
            )
        );

        return $row !== null ? (array) $row : false;
    }

    public function getBuddiesByUserId(int $userId): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT *
                    FROM `' . BUDDY . "`
                    WHERE `buddy_sender` = '" . (int) $userId . "'
                        OR `buddy_receiver` = '" . (int) $userId . "'"
                )
            )
        );
    }

    public function getBuddyDataById(int $userId): ?array
    {
        $row = DB::selectOne(
            $this->prepareSql(
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
            )
        );

        return $row !== null ? (array) $row : null;
    }

    public function removeBuddyById($buddy_id, $userId)
    {
        DB::statement(
            $this->prepareSql(
                'DELETE FROM `' . BUDDY . "`
                WHERE `buddy_id` = '" . (int) $buddy_id . "'
                    AND (`buddy_receiver` = '" . (int) $userId . "'
                            OR `buddy_sender` = '" . (int) $userId . "') "
            )
        );
    }

    public function setBuddyStatusById($buddy_id, $userId)
    {
        DB::statement(
            $this->prepareSql(
                'UPDATE `' . BUDDY . "`
                    SET `buddy_status` = '1'
                WHERE `buddy_id` = '" . (int) $buddy_id . "'
                    AND `buddy_receiver` = '" . (int) $userId . "'"
            )
        );
    }

    public function getBuddyIdByReceiverAndSender(int $sendTo, int $userId): ?array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT `buddy_id`
                FROM `' . BUDDY . "`
                WHERE (
                    `buddy_receiver` = '" . $userId . "'
                    AND `buddy_sender` = '" . $sendTo . "'
                ) OR (
                    `buddy_receiver` = '" . $sendTo . "'
                    AND `buddy_sender` = '" . $userId . "'
                )"
            )
        );

        return $row !== null ? (array) $row : null;
    }

    public function insertNewBuddyRequest(int $user, int $userId, string $text): void
    {
        DB::statement(
            $this->prepareSql(
                'INSERT INTO `' . BUDDY . "` SET
                    `buddy_sender` = '" . (int) $userId . "',
                    `buddy_receiver` = '" . (int) $user . "',
                    `buddy_status` = '0',
                    `buddy_request_text` = ?"
            ),
            [strip_tags($text)]
        );
    }

    public function checkIfBuddyExists(int $userId): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT `id`, `name`
                FROM `' . USERS . "`
                WHERE `id` = '" . $userId . "'"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function getBuddiesDetailsForAcsById(int $userId, int $group_id): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT DISTINCT u.`id`, u.`name`
                    FROM `' . BUDDY . '` AS b
                    LEFT JOIN `' . USERS . "` AS u
                        ON ((u.id = b.buddy_sender) OR (u.id = b.buddy_receiver))
                    WHERE (
                        b.`buddy_sender` = '" . $userId . "'
                        OR b.`buddy_receiver` = '" . $userId . "'
                    )
                    AND b.`buddy_status` = '1'
                    AND u.`id` NOT IN (
                        SELECT acs.`acs_user_id`
                        FROM `" . ACS_MEMBERS . "` acs
                        WHERE acs.`acs_group_id` = '" . $group_id . "'
                    )"
                )
            )
        );
    }
}
