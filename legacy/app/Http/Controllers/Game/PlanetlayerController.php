<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Services\FormatService;
use App\Services\Game\HomePlanetService;
use App\Models\Planets;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use App\Enums\Module;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class PlanetlayerController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private array $planet = [];

    public function __construct(
        private FormatService $formatService,
        private HomePlanetService $homePlanetService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Overview));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->buildPage();
    }

    private function buildPage(): void
    {
        $hasColonies = Planets::where(['planet_user_id' => $this->user['id'], 'planet_type' => 1, 'planet_destroyed' => 0])->count() > 1;
        $isMoon = $this->planet['planet_type'] == PlanetTypesEnumerator::MOON ? true : false;
        $defaultName = __('game/planetlayer.new_planet_name');

        if ($isMoon) {
            $defaultName = __('game/planetlayer.new_moon_name');
        }

        if (isset($_POST['planetName'])) {
            $this->renamePlanet();
        }

        if (isset($_POST['password']) && $hasColonies) {
            $this->deletePlanet();
        }

        Template::legacyView(
            'planetlayer.view',
            [
                'planetImage' => $this->planet['planet_image'],
                'mainPlanet' => ((int) $this->user['home_planet_id'] === (int) $this->planet['planet_id']),
                'withColonies' => $hasColonies,
                'isMoon' => $isMoon,
                'defaultName' => $defaultName,
                'planetCoords' => $this->formatService->formatCoords((int)$this->planet['planet_galaxy'], (int)$this->planet['planet_system'], (int)$this->planet['planet_planet']),
                'planetName' => $this->planet['planet_name'],
            ]
        );
    }

    private function renamePlanet(): void
    {
        $newName = filter_input(INPUT_POST, 'planetName');
        $newName = strip_tags(trim($newName));

        if (preg_match("/[^A-z0-9_\- ]/", $newName) == 1) {
            Functions::popupMessage(__('game/planetlayer.rename_error'), 'game.php?page=planetlayer', 3);
        }

        if ($newName != '') {
            DB::statement(
                $this->prepareSql('UPDATE `' . PLANETS . '` SET `planet_name` = ? WHERE `planet_id` = ? LIMIT 1;'),
                [$newName, $this->user['current_planet']]
            );
            Functions::popupMessage(__('game/planetlayer.rename_success', ['name' => $newName]), 'game.php?page=planetlayer', 3);
        }
    }

    private function deletePlanet()
    {
        $own_fleet = 0;
        $enemy_fleet = 0;
        $fleets_incoming = array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT
                        `fleet_owner`,
                        `fleet_target_owner`,
                        `fleet_end_type`,
                        `fleet_mess`
                    FROM `' . FLEETS . "`
                    WHERE (
                            fleet_owner = '" . $this->user['id'] . "' AND
                            fleet_start_galaxy = '" . $this->planet['planet_galaxy'] . "' AND
                            fleet_start_system = '" . $this->planet['planet_system'] . "' AND
                            fleet_start_planet = '" . $this->planet['planet_planet'] . "'
                    )
                    OR
                    (
                        fleet_target_owner = '" . $this->user['id'] . "' AND
                        fleet_end_galaxy = '" . $this->planet['planet_galaxy'] . "' AND
                        fleet_end_system = '" . $this->planet['planet_system'] . "' AND
                        fleet_end_planet = '" . $this->planet['planet_planet'] . "'
                    )"
                )
            )
        );

        $end_type = 0;
        $mess = 0;

        foreach ($fleets_incoming as $fleet) {
            $own_fleet = $fleet['fleet_owner'];
            $enemy_fleet = $fleet['fleet_target_owner'];

            if ($fleet['fleet_target_owner'] == $this->user['id']) {
                $end_type = $fleet['fleet_end_type'];
            }

            $mess = $fleet['fleet_mess'];

            if ($own_fleet > 0) {
                Functions::popupMessage(__('game/planetlayer.rp_abandon_planet_not_possible'), 'game.php?page=planetlayer', 3);
            } elseif ((($enemy_fleet > 0) && ($mess < 1)) && $end_type != 2) {
                Functions::popupMessage(__('game/planetlayer.rp_abandon_planet_not_possible'), 'game.php?page=planetlayer', 3);
            }
        }

        if (password_verify($_POST['password'], $this->user['password'])) {
            if ($this->planet['moon_id'] != 0) {
                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . PLANETS . '` AS p, `' . PLANETS . '` AS m, `' . USERS . "` AS u SET
                            p.`planet_destroyed` = '" . (time() + (PLANETS_LIFE_TIME * 3600)) . "',
                            m.`planet_destroyed` = '" . (time() + (PLANETS_LIFE_TIME * 3600)) . "',
                            u.`current_planet` = u.`home_planet_id`
                        WHERE p.`planet_id` = '" . $this->user['current_planet'] . "' AND
                            m.`planet_galaxy` = '" . $this->planet['planet_galaxy'] . "' AND
                            m.`planet_system` = '" . $this->planet['planet_system'] . "' AND
                            m.`planet_planet` = '" . $this->planet['planet_planet'] . "' AND
                            m.`planet_type` = '3' AND
                            u.`id` = '" . $this->user['id'] . "';"
                    )
                );
            } else {
                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . PLANETS . '` AS p, `' . USERS . "` AS u SET
                            p.`planet_destroyed` = '" . (time() + (PLANETS_LIFE_TIME * 3600)) . "',
                            u.`current_planet` = u.`home_planet_id`
                        WHERE p.`planet_id` = '" . $this->user['current_planet'] . "' AND
                            u.`id` = '" . $this->user['id'] . "';"
                    )
                );
            }

            $this->homePlanetService->moveCurrentAfterAbandoningPlanet(
                (int) $this->user['id'],
                (int) $this->user['current_planet'],
                (int) $this->user['home_planet_id']
            );

            Functions::popupMessage(__('game/planetlayer.rp_planet_abandoned'), 'game.php?page=planetlayer', 3);
        } else {
            Functions::popupMessage(__('game/planetlayer.wrong_password'), 'game.php?page=planetlayer', 3);
        }
    }
}
