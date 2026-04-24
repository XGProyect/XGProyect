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
class Infos
{
    use PreparesLegacySql;

    public function getTargetGate(int $target_planet_id): array
    {
        $row = DB::selectOne(
            $this->prepareSql(
                'SELECT
                    p.`planet_id`,
                    b.`building_jump_gate`,
                    p.`planet_last_jump_time`
                FROM `' . PLANETS . '` AS p
                INNER JOIN `' . BUILDINGS . "` AS b
                    ON b.`building_planet_id` = p.`planet_id`
                WHERE p.`planet_id` = '" . $target_planet_id . "';"
            )
        );

        return $row !== null ? (array) $row : [];
    }

    public function doJump(string $sub_query_origin, string $sub_query_destiny, int $jump_time, int $current_planet_id, int $target_planet_id, int $userId): void
    {
        DB::transaction(function () use ($sub_query_origin, $sub_query_destiny, $jump_time, $current_planet_id, $target_planet_id, $userId): void {
            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . '`, `' . USERS . '`, `' . SHIPS . "` SET
                        $sub_query_origin
                        `planet_last_jump_time` = '" . $jump_time . "',
                        `current_planet` = '" . $target_planet_id . "'
                    WHERE `planet_id` = '" . $current_planet_id . "'
                        AND `ship_planet_id` = '" . $current_planet_id . "'
                        AND `id` = '" . $userId . "';"
                )
            );

            DB::statement(
                $this->prepareSql(
                    'UPDATE `' . PLANETS . '`, `' . SHIPS . "` SET
                    $sub_query_destiny
                    `planet_last_jump_time` = '" . $jump_time . "'
                    WHERE `planet_id` = '" . $target_planet_id . "'
                        AND `ship_planet_id` = '" . $target_planet_id . "';"
                )
            );
        });
    }

    public function getListOfMoons(int $userId): array
    {
        return array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        m.`planet_id`,
                        m.`planet_galaxy`,
                        m.`planet_system`,
                        m.`planet_planet`,
                        m.`planet_name`,
                        m.`planet_last_jump_time`,
                        b.`building_jump_gate`
                    FROM `' . PLANETS . '` AS m
                    INNER JOIN `' . BUILDINGS . "` AS b ON b.building_planet_id = m.planet_id
                    WHERE m.`planet_type` = '3'
                        AND m.`planet_user_id` = '" . $userId . "';"
                )
            )
        );
    }
}
