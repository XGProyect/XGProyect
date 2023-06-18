<?php

namespace Xgp\App\Http\Controllers\Game;

use App\Models\Planets;
use App\Models\User;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Renameplanet;

class PlanetlayerController extends BaseController
{
    public const MODULE_ID = 1;

    private array $user = [];
    private array $planet = [];
    private Renameplanet $renameplanetModel;

    public function __invoke()
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->renameplanetModel = new Renameplanet();

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
                'mainPlanet' => ($this->user['home_planet_id'] === $this->planet['planet_id']),
                'withColonies' => $hasColonies,
                'isMoon' => $isMoon,
                'defaultName' => $defaultName,
                'planetCoords' => FormatLib::formatCoords($this->planet['planet_galaxy'], $this->planet['planet_system'], $this->planet['planet_planet']),
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
            $this->renameplanetModel->updatePlanetName($newName, $this->user['current_planet']);
            Functions::popupMessage(__('game/planetlayer.rename_success', ['name' => $newName]), 'game.php?page=planetlayer', 3);
        }
    }

    private function deletePlanet()
    {
        $own_fleet = 0;
        $enemy_fleet = 0;
        $fleets_incoming = $this->renameplanetModel->getFleets(
            $this->user['id'],
            $this->planet['planet_galaxy'],
            $this->planet['planet_system'],
            $this->planet['planet_planet']
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
                $this->renameplanetModel->deleteMoonAndPlanet(
                    $this->user['id'],
                    $this->user['current_planet'],
                    $this->planet['planet_galaxy'],
                    $this->planet['planet_system'],
                    $this->planet['planet_planet']
                );
            } else {
                $this->renameplanetModel->deletePlanet($this->user['id'], $this->user['current_planet']);
            }

            $nextPlanet = Planets::where([
                'planet_user_id' => $this->user['id'],
                'planet_type' => 1,
                'planet_destroyed' => 0,
            ])->firstOrFail()->planet_id;

            User::where(['id' => $this->user['id']])->update([
                'home_planet_id' => $nextPlanet,
                'current_planet' => $nextPlanet
            ]);

            Functions::popupMessage(__('game/planetlayer.rp_planet_abandoned'), 'game.php?page=planetlayer', 3);
        } else {
            Functions::popupMessage(__('game/planetlayer.wrong_password'), 'game.php?page=planetlayer', 3);
        }
    }
}
