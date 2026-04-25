<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Core\GameObjects\Building;
use App\Core\GameObjects\Defense;
use App\Core\GameObjects\GameObjectInterface;
use App\Core\GameObjects\GameObjectRegistry;
use App\Core\GameObjects\Research;
use App\Core\GameObjects\Ship;
use App\Enums\Module;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use RuntimeException;
use Xgp\App\Libraries\Functions;

/**
 * @SuppressWarnings("PHPMD.CouplingBetweenObjects")
 */
class TechnologydetailsController extends BaseController
{
    public function __construct(
        private GameObjectRegistry $registry,
        private SettingsService $settings,
    ) {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function __invoke(Request $request): View | RedirectResponse
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Information));

        $technology = $request->integer('technology');

        if (!$this->registry->has($technology)) {
            return redirect('game.php?page=technologytree');
        }

        $object = $this->registry->get($technology);

        return view('technologydetails.view', [
            'gameTitle' => $this->settings->getString('game_name'),
            'id' => $technology,
            'name' => __('game/' . $this->resolveObjectType($object) . '.' . $object->getName()),
            'description' => __('game/infos.info')[$object->getName()],
        ]);
    }

    private function resolveObjectType(GameObjectInterface $object): string
    {
        return match (true) {
            $object instanceof Building => 'constructions',
            $object instanceof Research => 'technologies',
            $object instanceof Ship => 'ships',
            $object instanceof Defense => 'defenses',
            default => throw new RuntimeException('Unknown object type for: ' . $object->getName()),
        };
    }
}
