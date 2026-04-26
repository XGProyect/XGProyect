<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use App\Services\FormatService;
use App\Services\Game\Formulas\DevelopmentsService;
use App\Services\SettingsService;
use Exception;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class EmpireController extends BaseController
{
    use PreparesLegacySql;

    private array $user = [];
    private Objects $objects;

    public function __construct(
        private FormatService $formatService,
        private DevelopmentsService $developmentsService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Empire));

        $this->user = Users::getInstance()->getUserData();
        $this->objects = Objects::getInstance();

        Template::legacyView(
            'empire.view',
            $this->buildBlocks()
        );
    }

    private function buildBlocks(): array
    {
        $userId = (int) $this->user['id'];
        $empireData = $userId > 0 ? array_map(
            fn ($row) => (array) $row,
            DB::select(
                $this->prepareSql(
                    'SELECT `planet_id`,
                        `planet_name`,
                        `planet_galaxy`,
                        `planet_system`,
                        `planet_planet`,
                        `planet_type`,
                        `planet_image`,
                        `planet_field_current`,
                        `planet_field_max`,
                        `planet_metal`,
                        `planet_metal_perhour`,
                        `planet_crystal`,
                        `planet_crystal_perhour`,
                        `planet_deuterium`,
                        `planet_deuterium_perhour`,
                        `planet_energy_used`,
                        `planet_energy_max`,
                        b.`building_metal_mine`,
                        b.`building_crystal_mine`,
                        b.`building_deuterium_sintetizer`,
                        b.`building_solar_plant`,
                        b.`building_fusion_reactor`,
                        b.`building_robot_factory`,
                        b.`building_nano_factory`,
                        b.`building_hangar`,
                        b.`building_metal_store`,
                        b.`building_crystal_store`,
                        b.`building_deuterium_tank`,
                        b.`building_laboratory`,
                        b.`building_terraformer`,
                        b.`building_ally_deposit`,
                        b.`building_missile_silo`,
                        b.`building_mondbasis`,
                        b.`building_phalanx`,
                        b.`building_jump_gate`,
                        d.`defense_rocket_launcher`,
                        d.`defense_light_laser`,
                        d.`defense_heavy_laser`,
                        d.`defense_gauss_cannon`,
                        d.`defense_ion_cannon`,
                        d.`defense_plasma_turret`,
                        d.`defense_small_shield_dome`,
                        d.`defense_large_shield_dome`,
                        d.`defense_anti-ballistic_missile`,
                        d.`defense_interplanetary_missile`,
                        s.`ship_small_cargo_ship`,
                        s.`ship_big_cargo_ship`,
                        s.`ship_light_fighter`,
                        s.`ship_heavy_fighter`,
                        s.`ship_cruiser`,
                        s.`ship_battleship`,
                        s.`ship_colony_ship`,
                        s.`ship_recycler`,
                        s.`ship_espionage_probe`,
                        s.`ship_bomber`,
                        s.`ship_solar_satellite`,
                        s.`ship_destroyer`,
                        s.`ship_deathstar`,
                        s.`ship_battlecruiser`
                    FROM `' . PLANETS . '` AS p
                    INNER JOIN `' . BUILDINGS . '` AS b ON b.building_planet_id = p.`planet_id`
                    INNER JOIN `' . DEFENSES . '` AS d ON d.defense_planet_id = p.`planet_id`
                    INNER JOIN `' . SHIPS . "` AS s ON s.ship_planet_id = p.`planet_id`
                    WHERE `planet_user_id` = '" . $userId . "'
                        AND `planet_destroyed` = 0;"
                )
            )
        ) : [];
        $empire = [];
        $resourceData = [
            'resources' => 'constructions',
            'facilities' => 'constructions',
            'fleet' => 'ships',
            'defenses' => 'defenses',
            'missiles' => 'defenses',
            'tech' => 'technologies',
        ];

        foreach ($empireData as $planet) {
            // general data
            foreach (['image', 'name', 'coords', 'fields'] as $element) {
                $empire[$element][] = $this->{'set' . ucfirst($element)}($planet);
            }

            // resources data
            foreach (['metal', 'crystal', 'deuterium', 'energy'] as $element) {
                $empire[$element . 'Row'][] = $this->setResources($planet, $element);
            }

            // structures and technologies data
            foreach ($resourceData as $element => $langLine) {
                $source = $planet;

                if ($element == 'tech') {
                    $source = $this->user;
                }

                foreach ($this->objects->getObjectsList($element) as $elementId) {
                    if (!isset($empire[$element][$this->objects->getObjects($elementId)])) {
                        $empire[$element][$this->objects->getObjects($elementId)]['value'] = '<th width="75px">' . __('game/' . $langLine . '.' . $this->objects->getObjects($elementId)) . '</th>';
                    }

                    $empire[$element][$this->objects->getObjects($elementId)]['value'] .= '<th width="75px">' . $this->setStructureData($planet, $source, $element, $elementId) . '</th>';
                }
            }
        }

        return array_merge(
            [
                'planetsAmount' => count($empireData) + 1,
            ],
            $empire
        );
    }

    private function setImage(array $planet): array
    {
        return [
            'planetId' => $planet['planet_id'],
            'planetImage' => $planet['planet_image'],
            'planetName' => $planet['planet_name'],
        ];
    }

    private function setName(array $planet): array
    {
        return [
            'planetName' => $planet['planet_name'],
        ];
    }

    private function setCoords(array $planet): array
    {
        return [
            'planetCoords' => $this->formatService->prettyCoords((int) $planet['planet_galaxy'], (int) $planet['planet_system'], (int) $planet['planet_planet']),
            'planetGalaxy' => $planet['planet_galaxy'],
            'planetSystem' => $planet['planet_system'],
        ];
    }

    private function setFields(array $planet): array
    {
        return [
            'planetFieldCurrent' => $planet['planet_field_current'],
            'planetFieldMax' => $planet['planet_field_max'],
        ];
    }

    private function setResources(array $planet, string $resource): array
    {
        if ($resource == 'energy') {
            return [
                'usedEnergy' => ($this->formatService->prettyNumber(
                    ((int)($planet['planet_energy_max'] + $planet['planet_energy_used']))
                )),
                'maxEnergy' => $this->formatService->prettyNumber((int)$planet['planet_energy_max']),
            ];
        }

        return [
            'planetId' => $planet['planet_id'],
            'planetType' => $planet['planet_type'],
            'planetCurrentAmount' => $this->formatService->prettyNumber((int) $planet['planet_' . $resource]),
            'planetProduction' => (
                $this->formatService->prettyNumber(
                    ((int)($planet['planet_' . $resource . '_perhour'] + app(SettingsService::class)->getInt($resource . '_basic_income')))
                )
            ),
        ];
    }

    private function setStructureData(array $planet, array $source, string $element, int $elementId): string
    {
        switch ($element) {
            case 'resources':
            case 'facilities':
                $page = $this->developmentsService->setBuildingPage($elementId);
                break;
            case 'tech':
                $page = 'research';
                break;
            case 'fleet':
                $page = 'shipyard';
                break;
            case 'defenses':
            case 'missiles':
                $page = 'defense';
                break;
            default:
                throw new Exception('Undefined element type "' . $element . '". Only possible: build, tech, fleet, defenses and missiles.');
                break;
        }

        $url = 'game.php?page=' . $page . '&cp=' . $planet['planet_id'] . '&re=0&planettype=' . $planet['planet_type'];

        return app(FormatService::class)->link(
            $url,
            (string) $source[$this->objects->getObjects($elementId)]
        );
    }
}
