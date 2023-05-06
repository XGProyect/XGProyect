<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Renameplanet;

class RenameplanetController extends BaseController
{
    public const MODULE_ID = 1;

    private array $user = [];
    private array $planet = [];
    private Renameplanet $renameplanetModel;

    public function __invoke()
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->renameplanetModel = new Renameplanet();

        $this->buildPage();
    }

    private function buildPage(): void
    {
        $parse['planet_name'] = $this->planet['planet_name'];
        $parse['planet_id'] = $this->planet['planet_id'];
        $parse['galaxy_galaxy'] = $this->planet['planet_galaxy'];
        $parse['galaxy_system'] = $this->planet['planet_system'];
        $parse['galaxy_planet'] = $this->planet['planet_planet'];

        // DEFAULT VIEW
        $current_view = 'renameplanet/renameplanet_view';

        // CHANGE THE ACTION
        switch ((isset($_POST['action']) ? $_POST['action'] : null)) {
            case __('game/renameplanet.rp_planet_rename_action'):
                $this->rename_planet($_POST['newname']);
                break;
            case __('game/renameplanet.rp_abandon_planet'):
                // DELETE VIEW
                $current_view = 'renameplanet/renameplanet_delete_view';
                break;
        } // switch

        if (isset($_POST['kolonieloeschen']) && (int) $_POST['kolonieloeschen'] == 1 && (int) $_POST['deleteid'] == $this->user['user_current_planet']) {
            $this->delete_planet();
        }

        Template::getInstance()->view(
            $current_view,
            $parse
        );
    }

    /**
     * method rename_planet
     * param $new_name
     * return main method, loads everything
     */
    private function rename_planet($new_name)
    {
        $new_name = strip_tags(trim($new_name));

        if (preg_match("/[^A-z0-9_\- ]/", $new_name) == 1) {
            Functions::message(__('game/renameplanet.rp_newname_error'), 'game.php?page=renameplanet', 2);
        }

        if ($new_name != '') {
            $this->renameplanetModel->updatePlanetName($new_name, $this->user['user_current_planet']);
            Functions::message(__('game/renameplanet.rp_planet_name_changed'), 'game.php?page=renameplanet', 2);
        }
    }

    /**
     * method delete_planet
     * param
     * return deletes the planet
     */
    private function delete_planet()
    {
        $own_fleet = 0;
        $enemy_fleet = 0;
        $fleets_incoming = $this->renameplanetModel->getFleets(
            $this->user['user_id'],
            $this->planet['planet_galaxy'],
            $this->planet['planet_system'],
            $this->planet['planet_planet']
        );

        foreach ($fleets_incoming as $fleet) {
            $own_fleet = $fleet['fleet_owner'];
            $enemy_fleet = $fleet['fleet_target_owner'];

            if ($fleet['fleet_target_owner'] == $this->user['user_id']) {
                $end_type = $fleet['fleet_end_type'];
            }

            $mess = $fleet['fleet_mess'];
        }

        if ($own_fleet > 0) {
            Functions::message(__('game/renameplanet.rp_abandon_planet_not_possible'), 'game.php?page=renameplanet');
        } elseif ((($enemy_fleet > 0) && ($mess < 1)) && $end_type != 2) {
            Functions::message(__('game/renameplanet.rp_abandon_planet_not_possible'), 'game.php?page=renameplanet');
        } else {
            if (password_verify($_POST['pw'], $this->user['user_password']) && $this->user['user_home_planet_id'] != $this->user['user_current_planet']) {
                if ($this->planet['moon_id'] != 0) {
                    $this->renameplanetModel->deleteMoonAndPlanet(
                        $this->user['user_id'],
                        $this->user['user_current_planet'],
                        $this->planet['planet_galaxy'],
                        $this->planet['planet_system'],
                        $this->planet['planet_planet']
                    );
                } else {
                    $this->renameplanetModel->deletePlanet($this->user['user_id'], $this->user['user_current_planet']);
                }

                Functions::message(__('game/renameplanet.rp_planet_abandoned'), 'game.php?page=overview');
            } elseif ($this->user['user_home_planet_id'] == $this->user['user_current_planet']) {
                Functions::message(__('game/renameplanet.rp_principal_planet_cant_abanone'), 'game.php?page=renameplanet');
            } else {
                Functions::message(__('game/renameplanet.rp_wrong_pass'), 'game.php?page=renameplanet');
            }
        }
    }
}
