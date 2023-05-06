<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class TechtreeController extends BaseController
{
    public const MODULE_ID = 10;

    private array $user = [];
    private array $planet = [];
    private $_resource;
    private $_requirements;

    public function __invoke()
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();

        // requirements
        $this->_resource = Objects::getInstance()->getObjects();

        // requirements
        $this->_requirements = Objects::getInstance()->getRelations();

        // Check module access
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->buildPage();
    }

    private function buildPage(): void
    {
        Template::getInstance()->view(
            'game/techtree_view',
            [
                'list_of_constructions' => $this->buildBlock('build'),
                'list_of_research' => $this->buildBlock('tech'),
                'list_of_ships' => $this->buildBlock('fleet'),
                'list_of_defenses' => $this->buildBlock('defenses'),
                'list_of_missiles' => $this->buildBlock('missiles'),
            ]
        );
    }

    private function buildBlock(string $object_id): array
    {
        $objects = Objects::getInstance()->getObjectsList($object_id);
        $list_of_objects = [];

        foreach ($objects as $object) {
            $list_of_objects[] = [
                'tt_info' => $object,
                'tt_name' => $this->langs->language[$this->_resource[$object]],
                'tt_detail' => '',
                'requirements' => join('<br/>', $this->getRequirements($object)),
            ];
        }

        return $list_of_objects;
    }

    /**
     * Build the requirements list
     *
     * @param int $object
     *
     * @return array
     */
    private function getRequirements(int $object): array
    {
        $list_of_requirements = [];

        if (!isset($this->_requirements[$object])) {
            return $list_of_requirements;
        }

        foreach ($this->_requirements[$object] as $requirement => $level) {
            $color = 'Red';

            if ((isset($this->user[$this->_resource[$requirement]])
                && $this->user[$this->_resource[$requirement]] >= $level)
                or (isset($this->planet[$this->_resource[$requirement]])
                    && $this->planet[$this->_resource[$requirement]] >= $level)) {
                $color = 'Green';
            }

            $list_of_requirements[] = FormatLib::{'color' . $color}(
                FormatLib::formatLevel(
                    $this->langs->language[$this->_resource[$requirement]],
                    $level
                )
            );
        }

        return $list_of_requirements;
    }
}
