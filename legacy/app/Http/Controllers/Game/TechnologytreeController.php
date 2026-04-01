<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class TechnologytreeController extends BaseController
{
    public const MODULE_ID = 10;

    private array $user = [];
    private array $planet = [];
    private $_resource;
    private $_requirements;

    public function __construct(private FormatService $formatService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->_resource = Objects::getInstance()->getObjects();
        $this->_requirements = Objects::getInstance()->getRelations();

        $this->buildPage();
    }

    private function buildPage(): void
    {
        Template::legacyView(
            'technologytree.view',
            [
                'list_of_constructions' => $this->buildBlock('build'),
                'list_of_research' => $this->buildBlock('tech'),
                'list_of_ships' => $this->buildBlock('fleet'),
                'list_of_defenses' => $this->buildBlock('defenses'),
                'list_of_missiles' => $this->buildBlock('missiles'),
            ]
        );
    }

    private function buildBlock(string $objectId): array
    {
        $objects = Objects::getInstance()->getObjectsList($objectId);
        $langFile = [
            'build' => 'constructions',
            'tech' => 'technologies',
            'fleet' => 'ships',
            'defenses' => 'defenses',
            'missiles' => 'defenses',
        ][$objectId];
        $objectsList = [];

        foreach ($objects as $object) {
            $objectsList[] = [
                'tt_info' => $object,
                'tt_name' => __('game/' . $langFile . '.' . $this->_resource[$object]),
                'tt_detail' => '',
                'requirements' => join('<br>', $this->getRequirements($object)),
            ];
        }

        return $objectsList;
    }

    private function getRequirements(int $object, int $currentLevel = 0): array
    {
        $requirementsList = [];

        if (!isset($this->_requirements[$object])) {
            return $requirementsList;
        }

        foreach ($this->_requirements[$object] as $requirement => $requiredLevel) {
            $color = 'Red';

            $currentResourceLevel = $this->planet[$this->_resource[$requirement]] ?? $this->user[$this->_resource[$requirement]] ?? 0;
            $currentResourceLevel = max($currentResourceLevel, $currentLevel);

            if ($currentResourceLevel >= $requiredLevel) {
                $color = 'Green';
                $displayLevel = $requiredLevel;
            } else {
                $displayLevel = $currentResourceLevel . '/' . $requiredLevel;
            }

            $requirementsList[] = $this->formatService->{'color' . $color}(
                $this->formatService->formatLevel(
                    $this->setRequirementText($this->_resource[$requirement]),
                    (int) $displayLevel
                )
            );
        }

        return $requirementsList;
    }

    private function setRequirementText(string $requirement): string
    {
        $langFile = '';
        $langTypeMap = [
            'building_' => 'constructions',
            'research_' => 'technologies',
            'ship_' => 'ships',
            'defense_' => 'defenses',
        ];

        foreach ($langTypeMap as $type => $lang) {
            if (strpos($requirement, $type) !== false) {
                $langFile = $lang;
                break;
            }
        }

        return __('game/' . $langFile . '.' . $requirement);
    }
}
