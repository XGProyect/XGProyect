<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use Exception;
use Illuminate\Routing\Controller as BaseController;
use App\Services\SettingsService;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Helpers\UrlHelper;
use Xgp\App\Libraries\DevelopmentsLib;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Empire;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class EmpireController extends BaseController
{
    public const MODULE_ID = 2;

    private array $user = [];
    private Empire $empireModel;
    private Objects $objects;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->objects = Objects::getInstance();
        $this->empireModel = new Empire();

        Template::legacyView(
            'empire.view',
            $this->buildBlocks()
        );
    }

    private function buildBlocks(): array
    {
        $empireData = $this->empireModel->getAllPlayerData((int) $this->user['id']);
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
            'planetCoords' => FormatLib::prettyCoords((int) $planet['planet_galaxy'], (int) $planet['planet_system'], (int) $planet['planet_planet']),
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
                'usedEnergy' => (FormatLib::prettyNumber(
                    ((int)($planet['planet_energy_max'] + $planet['planet_energy_used']))
                )),
                'maxEnergy' => FormatLib::prettyNumber((int)$planet['planet_energy_max']),
            ];
        }

        return [
            'planetId' => $planet['planet_id'],
            'planetType' => $planet['planet_type'],
            'planetCurrentAmount' => FormatLib::prettyNumber((int) $planet['planet_' . $resource]),
            'planetProduction' => (
                FormatLib::prettyNumber(
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
                $page = DevelopmentsLib::setBuildingPage($elementId);
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

        return UrlHelper::setUrl(
            $url,
            $source[$this->objects->getObjects($elementId)]
        );
    }
}
