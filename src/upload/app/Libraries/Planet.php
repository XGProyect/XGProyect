<?php declare (strict_types = 1);
/**
 * XG Proyect
 *
 * Open-source OGame Clon
 *
 * This content is released under the GPL-3.0 License
 *
 * Copyright (c) 2008-2021 XG Proyect
 *
 * @package    XG Proyect
 * @author     XG Proyect Team
 * @copyright  2008-2021 XG Proyect
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0 License
 * @link       https://github.com/XGProyect/
 * @since      4.0.0
 */
namespace App\Libraries;

use App\Entities\Coordinates;
use Config\DBSettings;

class Planet
{
    /**
     * @var App\Models\PlanetModel
     */
    private $planetModel;

    /**
     * Contains the settings
     *
     * @var Config\DBSettings
     */
    private $setting;

    /**
     * Constructor
     */
    public function __construct()
    {
        // load models
        $this->planetModel = model('App\Models\PlanetModel');

        // load settings
        $this->setting = new DBSettings;
    }

    /**
     * Get the position for a new planet. If no position is found returns null
     *
     * @param integer $lastGalaxy
     * @param integer $lastSystem
     * @param integer $lastPlanet Called like this for simplicity, but it actually refers to the maximum amount of planets that can be set per system
     * @return Coordinates
     */
    public function getNewPlanetPosition(int $lastGalaxy = 0, int $lastSystem = 0, int $lastPlanet = 0): ?Coordinates
    {
        if ($lastGalaxy == 0 && $lastSystem == 0 && $lastPlanet == 0) {
            $lastGalaxy = (int) $this->setting->one('lastsettedgalaxypos');
            $lastSystem = (int) $this->setting->one('lastsettedsystempos');
            $lastPlanet = (int) $this->setting->one('lastsettedplanetpos');
        }

        if ($lastGalaxy <= MAX_GALAXY_IN_WORLD) {
            $galaxy = $lastGalaxy;
        }

        if ($lastSystem <= MAX_SYSTEM_IN_GALAXY) {
            $system = $lastSystem;
        }

        if ($lastPlanet < 4) {
            $lastPlanet += 1;
        } else {
            if ($lastSystem == MAX_SYSTEM_IN_GALAXY) {
                $lastGalaxy += 1;
                $lastSystem = 1;
            } else {
                $lastSystem += 1;
            }

            $lastPlanet = 1;
        }

        // set new coordinates
        $coords = new Coordinates;
        $coords->galaxy = $galaxy;
        $coords->system = $system;
        $coords->planet = mt_rand(4, 12);

        // check if the position is available
        if ($this->planetModel->isPositionAvailable($coords)) {
            $this->setting->save('lastsettedgalaxypos', (string) $lastGalaxy);
            $this->setting->save('lastsettedsystempos', (string) $lastSystem);
            $this->setting->save('lastsettedplanetpos', (string) $lastPlanet);

            return $coords;
        }

        // check next coords
        $this->getNewPlanetPosition($lastGalaxy, $lastSystem, $lastPlanet);
    }
}
