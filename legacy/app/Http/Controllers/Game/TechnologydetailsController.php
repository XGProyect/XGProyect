<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Enums\Module;
use Exception;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class TechnologydetailsController extends BaseController
{
    private array $user = [];
    private array $planet = [];
    private Objects $objects;

    private int $technology;

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Information));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->objects = Objects::getInstance();

        $this->technology = filter_input(INPUT_GET, 'technology', FILTER_VALIDATE_INT);

        if (!array_key_exists($this->technology, $this->objects->getObjects())) {
            Functions::redirect('game.php?page=technologytree');
        }

        $objectType = $this->getObjectType();

        Template::legacyView(
            'technologydetails.view',
            [
                'id' => $this->technology,
                'name' => __('game/' . $objectType . '.' . $this->objects->getObjects($this->technology)),
                'description' => __('game/infos.info')[$this->objects->getObjects($this->technology)],
            ]
        );
    }

    private function getObjectType(): string
    {
        $typeMap = [
            'building_' => 'constructions',
            'research_' => 'technologies',
            'ship_' => 'ships',
            'defense_' => 'defenses',
        ];

        foreach ($typeMap as $typePart => $type) {
            if (strpos($this->objects->getObjects($this->technology), $typePart) !== false) {
                return $type;
            }
        }

        throw new Exception('Type couldn\'t be defined which is a fatal error, all objects need a type!');
    }
}
