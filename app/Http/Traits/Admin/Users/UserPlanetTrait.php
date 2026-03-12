<?php

declare(strict_types=1);

namespace App\Http\Traits\Admin\Users;

use DirectoryIterator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use stdClass;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Libraries\StatisticsLibrary;

/**
 * Shared helpers used by UserPlanetController, UserMoonController and UsersController.
 *
 * Provides all planet/moon data-access, persistence, and view-preparation logic
 * so each controller stays lean and free of duplication.
 */
trait UserPlanetTrait
{
    // ── Data retrieval ────────────────────────────────────────────────────────

    /**
     * @return array<int, object>
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    private function getPlanetsWithMoons(int $userId): array
    {
        $prefix = DB::getTablePrefix();

        return array_map(
            fn (object $row): object => $row,
            DB::select(
                "SELECT p.planet_id, p.planet_name, p.planet_image, p.planet_galaxy, p.planet_system,
                        p.planet_planet, p.planet_destroyed,
                        m.planet_id AS moon_id, m.planet_name AS moon_name,
                        m.planet_image AS moon_image, m.planet_destroyed AS moon_destroyed
                 FROM `{$prefix}planets` AS p
                 LEFT JOIN `{$prefix}planets` AS m
                     ON m.planet_galaxy = p.planet_galaxy
                     AND m.planet_system = p.planet_system
                     AND m.planet_planet = p.planet_planet
                     AND m.planet_type = 3
                 WHERE p.planet_user_id = ? AND p.planet_type = 1
                 ORDER BY p.planet_galaxy, p.planet_system, p.planet_planet",
                [$userId]
            )
        );
    }

    /**
     * @return array<int, object>
     */
    private function getMoons(int $userId): array
    {
        return DB::table('planets')
            ->where('planet_user_id', $userId)
            ->where('planet_type', PlanetTypesEnumerator::MOON)
            ->orderBy('planet_galaxy')->orderBy('planet_system')->orderBy('planet_planet')
            ->get()->all();
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function getPlanetData(int $planetId, int $type): ?object
    {
        $prefix = DB::getTablePrefix();

        $result = DB::select(
            "SELECT p.*, b.*, s.*, d.*
             FROM `{$prefix}planets` AS p
             INNER JOIN `{$prefix}buildings` AS b ON b.building_planet_id = p.planet_id
             INNER JOIN `{$prefix}ships` AS s ON s.ship_planet_id = p.planet_id
             INNER JOIN `{$prefix}defenses` AS d ON d.defense_planet_id = p.planet_id
             WHERE p.planet_id = ? AND p.planet_type = ?",
            [$planetId, $type]
        );

        return $result ? (object) (array) $result[0] : null;
    }

    private function getBuildingsData(int $planetId): stdClass
    {
        return DB::table('buildings')->where('building_planet_id', $planetId)->first() ?? new stdClass();
    }

    private function getShipsData(int $planetId): stdClass
    {
        return DB::table('ships')->where('ship_planet_id', $planetId)->first() ?? new stdClass();
    }

    private function getDefensesData(int $planetId): stdClass
    {
        return DB::table('defenses')->where('defense_planet_id', $planetId)->first() ?? new stdClass();
    }

    // ── Persistence ───────────────────────────────────────────────────────────

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function savePlanetData(Request $request, int $planetId): void
    {
        $stringFields = ['planet_name', 'planet_image'];
        $skipFields = ['planet_b_building_id', 'planet_b_tech_id', 'planet_b_hangar_id'];
        $updates = [];

        // Explicitly handle the destroyed toggle — defaults to 0 (cancel) if not submitted
        $destroyedValue = $request->integer('planet_destroyed', 0);
        /** @phpstan-ignore constant.notFound */
        $updates['planet_destroyed'] = ($destroyedValue === 1) ? (time() + (PLANETS_LIFE_TIME * 3600)) : 0;

        foreach ($request->except(['_token', '_method', 'planet_destroyed', ...$skipFields]) as $field => $value) {
            if ($value === null) {
                continue; // skip nullable fields not submitted by this form variant (moon vs planet)
            }
            if (in_array($field, $stringFields, true)) {
                $updates[$field] = (string) $value;
            } elseif (is_string($field) && str_starts_with($field, 'planet_')) {
                $updates[$field] = is_numeric($value) ? (int) $value : (string) $value;
            }
        }

        if ($updates !== []) {
            DB::table('planets')->where('planet_id', $planetId)->update($updates);
        }
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function saveBuildingsData(Request $request, int $planetId, int $type): void
    {
        $updates = [];
        $totalFields = 0;

        foreach ($request->all() as $field => $value) {
            if (is_string($field) && str_starts_with($field, 'building_')) {
                $level = (int) $value; // @phpstan-ignore cast.int
                $updates[$field] = $level;
                $totalFields += $level;
            }
        }

        if ($updates !== []) {
            DB::table('buildings')->where('building_planet_id', $planetId)->update($updates);
        }

        $planetUpdate = ['planet_field_current' => $totalFields];

        if ($type === PlanetTypesEnumerator::MOON) {
            /** @phpstan-ignore constant.notFound */
            $planetUpdate['planet_field_max'] = 1 + $request->integer('building_mondbasis', 0) * FIELDS_BY_MOONBASIS_LEVEL;
        }

        DB::table('planets')->where('planet_id', $planetId)->update($planetUpdate);

        $userId = (int) DB::table('planets')->where('planet_id', $planetId)->value('planet_user_id'); // @phpstan-ignore cast.int
        (new StatisticsLibrary())->rebuildPoints($userId, $planetId, 'buildings');
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function saveShipsData(Request $request, int $planetId): void
    {
        $updates = [];

        foreach ($request->all() as $field => $value) {
            if (is_string($field) && str_starts_with($field, 'ship_')) {
                $updates[$field] = (int) $value; // @phpstan-ignore cast.int
            }
        }

        if ($updates !== []) {
            DB::table('ships')->where('ship_planet_id', $planetId)->update($updates);
        }

        $userId = (int) DB::table('planets')->where('planet_id', $planetId)->value('planet_user_id'); // @phpstan-ignore cast.int
        (new StatisticsLibrary())->rebuildPoints($userId, $planetId, 'ships');
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function saveDefensesData(Request $request, int $planetId): void
    {
        $updates = [];

        foreach ($request->all() as $field => $value) {
            if (is_string($field) && str_starts_with($field, 'defense_')) {
                $updates[$field] = (int) $value; // @phpstan-ignore cast.int
            }
        }

        if ($updates !== []) {
            DB::table('defenses')->where('defense_planet_id', $planetId)->update($updates);
        }

        $userId = (int) DB::table('planets')->where('planet_id', $planetId)->value('planet_user_id'); // @phpstan-ignore cast.int
        (new StatisticsLibrary())->rebuildPoints($userId, $planetId, 'defenses');
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function hardDeletePlanetRow(int $planetId, int $type): void
    {
        $prefix = DB::getTablePrefix();
        $alias = $type === PlanetTypesEnumerator::MOON ? 'm' : 'p';

        DB::statement(
            "DELETE {$alias}, b, s, d
             FROM `{$prefix}planets` AS {$alias}
             INNER JOIN `{$prefix}buildings` AS b ON b.`building_planet_id` = {$alias}.`planet_id`
             INNER JOIN `{$prefix}ships` AS s ON s.`ship_planet_id` = {$alias}.`planet_id`
             INNER JOIN `{$prefix}defenses` AS d ON d.`defense_planet_id` = {$alias}.`planet_id`
             WHERE {$alias}.`planet_id` = ? AND {$alias}.`planet_type` = ?",
            [$planetId, $type]
        );
    }

    /** Count the number of (non-destroyed) planets owned by a user. */
    private function countUserPlanets(int $userId): int
    {
        return (int) DB::table('planets')
            ->where('planet_user_id', $userId)
            ->where('planet_type', PlanetTypesEnumerator::PLANET)
            ->where('planet_destroyed', 0)
            ->count();
    }

    /**
     * Return the planet_id of the next best planet for a user after excluding one planet.
     * Ordered by galaxy → system → planet (ascending), so the "first" remaining planet is returned.
     */
    private function resolveNextHomePlanet(int $userId, int $excludePlanetId): ?int
    {
        $value = DB::table('planets')
            ->where('planet_user_id', $userId)
            ->where('planet_type', PlanetTypesEnumerator::PLANET)
            ->where('planet_destroyed', 0)
            ->where('planet_id', '!=', $excludePlanetId)
            ->orderBy('planet_galaxy')
            ->orderBy('planet_system')
            ->orderBy('planet_planet')
            ->value('planet_id');

        return $value !== null ? (int) $value : null; // @phpstan-ignore cast.int
    }

    // ── View preparation ──────────────────────────────────────────────────────

    /**
     * @return array<string, mixed>
     */
    private function preparePlanetViewData(object $planet, string $dateFormat): array
    {
        $data = (array) $planet;

        $data['planet_field_current'] = $this->countOccupiedFields((int) $data['planet_id']);
        $data['planet_last_update_display'] = date($dateFormat, (int) ($data['planet_last_update'] ?? 0));
        $data['is_destroyed'] = ($data['planet_destroyed'] ?? 0) > 0;
        $data['planet_destroyed_at'] = ($data['planet_destroyed'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_destroyed']) : null;
        $data['planet_b_building_display'] = ($data['planet_b_building'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_b_building']) : '-';
        $data['planet_b_tech_display'] = ($data['planet_b_tech'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_b_tech']) : '-';
        $data['planet_b_hangar_display'] = ($data['planet_b_hangar'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_b_hangar']) : '-';
        $data['planet_last_jump_display'] = ($data['planet_last_jump_time'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_last_jump_time']) : '-';
        $data['planet_invisible_start_display'] = ($data['planet_invisible_start_time'] ?? 0) > 0 ? date($dateFormat, (int) $data['planet_invisible_start_time']) : '-';

        return $data;
    }

    private function countOccupiedFields(int $planetId): int
    {
        $row = DB::table('buildings')
            ->where('building_planet_id', $planetId)
            ->selectRaw(
                'COALESCE(
                    building_metal_mine + building_crystal_mine + building_deuterium_sintetizer +
                    building_solar_plant + building_fusion_reactor + building_robot_factory +
                    building_nano_factory + building_hangar + building_metal_store +
                    building_crystal_store + building_deuterium_tank + building_laboratory +
                    building_terraformer + building_ally_deposit + building_missile_silo +
                    building_mondbasis + building_phalanx + building_jump_gate,
                0) AS total'
            )
            ->first();

        return $row ? (int) $row->total : 0;
    }

    private function buildProcessQueue(string $rawQueue): string
    {
        if (empty($rawQueue)) {
            return '<option value="">-</option>';
        }

        $html = '';

        foreach (explode(';', $rawQueue) as $item) {
            $parts = explode(',', $item);
            if (count($parts) < 5) {
                continue;
            }

            $ready = ((int) $parts[3] <= time()) ? 'OK' : date('i:s', (int) $parts[3] - time());
            $techName = __('admin/users.tech')[(int) $parts[0]] ?? "#{$parts[0]}";

            $html .= '<option value="' . $parts[0] . '">'
                . $techName . ' (' . $parts[1] . '^) (' . date('i:s', (int) $parts[2]) . ') (' . $ready . ') [' . $parts[4] . ']'
                . '</option>';
        }

        return $html ?: '<option value="">-</option>';
    }

    /**
     * @return array<string, string>
     */
    private function getPlanetImages(): array
    {
        $dir = public_path('assets/upload/skins/xgproyect/planets');
        $images = [];

        if (!is_dir($dir)) {
            return $images;
        }

        foreach (new DirectoryIterator($dir) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.jpg')) {
                $name = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getFilename()) ?? '';
                $images[$name] = $file->getFilename();
            }
        }

        ksort($images);

        return $images;
    }

    /**
     * @return array<int, string>
     */
    private function percentOptions(): array
    {
        $opts = [];

        for ($i = 0; $i <= 10; $i++) {
            $opts[$i] = ($i * 10) . '%';
        }

        return $opts;
    }

    // ── List builders ─────────────────────────────────────────────────────────

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildBuildingsList(object $row, int $type): array
    {
        $excludePlanet = ['building_mondbasis', 'building_phalanx', 'building_jump_gate'];
        $excludeMoon = [
            'building_metal_mine', 'building_crystal_mine', 'building_deuterium_sintetizer',
            'building_solar_plant', 'building_fusion_reactor', 'building_nano_factory',
            'building_laboratory', 'building_terraformer', 'building_ally_deposit', 'building_missile_silo',
        ];
        $exclude = $type === PlanetTypesEnumerator::MOON ? $excludeMoon : $excludePlanet;

        $list = [];
        $skip = 2;

        foreach ((array) $row as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, 'building_')) {
                continue;
            }
            if ($skip-- > 0) {
                continue;
            }
            if (in_array($key, $exclude, true)) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key), // @phpstan-ignore cast.string
                'level' => (int) $value,
            ];
        }

        return $list;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildShipsList(object $row): array
    {
        $list = [];
        $skip = 2;

        foreach ((array) $row as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, 'ship_')) {
                continue;
            }
            if ($skip-- > 0) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key), // @phpstan-ignore cast.string
                'amount' => (int) $value,
            ];
        }

        return $list;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildDefensesList(object $row, int $type): array
    {
        $excludeMoon = ['defense_anti-ballistic_missile', 'defense_interplanetary_missile'];
        $exclude = $type === PlanetTypesEnumerator::MOON ? $excludeMoon : [];

        $list = [];
        $skip = 2;

        foreach ((array) $row as $key => $value) {
            if (!is_string($key) || !str_starts_with($key, 'defense_')) {
                continue;
            }
            if ($skip-- > 0) {
                continue;
            }
            if (in_array($key, $exclude, true)) {
                continue;
            }

            $list[] = [
                'field' => $key,
                'label' => (string) __('admin/users.us_user_' . $key), // @phpstan-ignore cast.string
                'amount' => (int) $value,
            ];
        }

        return $list;
    }
}
