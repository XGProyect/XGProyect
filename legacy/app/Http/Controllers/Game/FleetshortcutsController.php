<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\PlanetTypesEnumerator;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Libraries\Users\Shortcuts;
use Xgp\App\Models\Game\Shortcuts as ShortcutsModel;

class FleetshortcutsController extends BaseController
{
    public const MODULE_ID = 8;
    public const REDIRECT_TARGET = 'game.php?page=shortcuts';

    private array $user = [];
    private ?Shortcuts $_shortcuts = null;
    private array $_clean_data = [];
    private ShortcutsModel $shortcutsModel;

    public function __invoke()
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->shortcutsModel = new ShortcutsModel();

        $this->setUpShortcuts();
        $this->runAction();

        Template::legacyView(
            'fleet.shortcuts.view',
            [
                'shortcuts' => $this->buildShortcuts(),
            ]
        );
    }

    private function setUpShortcuts(): void
    {
        $this->_shortcuts = new Shortcuts(
            $this->user['fleet_shortcuts']
        );
    }

    private function runAction(): void
    {
        $mode = filter_input(
            INPUT_GET,
            'mode',
            FILTER_CALLBACK,
            [
                'options' => function ($value) {
                    if (in_array($value, ['add', 'edit', 'delete', 'a'])) {
                        return $value;
                    }

                    return false;
                },
            ]
        );

        $data = filter_input_array(INPUT_POST, [
            'name' => FILTER_UNSAFE_RAW,
            'galaxy' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => MAX_GALAXY_IN_WORLD],
            ],
            'system' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => MAX_SYSTEM_IN_GALAXY],
            ],
            'planet' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => (MAX_PLANET_IN_SYSTEM + 1)],
            ],
            'type' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => ['min_range' => 1, 'max_range' => 3],
            ],
        ]);

        $action = filter_input(INPUT_GET, 'a', FILTER_VALIDATE_INT);

        if ($mode) {
            $this->_clean_data['mode'] = $mode;
            $this->_clean_data['data'] = $data;
            $this->_clean_data['action'] = $action;

            $this->{$mode . 'Shortcut'}();
        }
    }

    private function buildShortcuts(): array
    {
        $shortcuts = $this->_shortcuts->getAllAsArray();
        $list_of_shortcuts = [];

        if ($shortcuts && count($shortcuts) > 0) {
            $set_row = true;
            $shortcut_id = 0;

            foreach ($shortcuts as $shortcut) {
                $list_of_shortcuts[] = [
                    'row_start' => $set_row ? '<tr height="20">' : '',
                    'shortcut_id' => $shortcut_id++,
                    'shortcut_name' => $shortcut['name'],
                    'shortcut_coords' => FormatLib::formatCoords(
                        $shortcut['g'],
                        $shortcut['s'],
                        $shortcut['p'],
                    ),
                    'shortcut_type' => __('game/global.planet_type_short')[$shortcut['pt']],
                    'row_end' => !$set_row ? '</tr>' : '',
                ];

                $set_row = !$set_row;
            }
        }

        return $list_of_shortcuts;
    }

    private function addShortcut(): void
    {
        $this->setData();
        $this->buildEdit([
            'mode' => 'add',
            'visibility' => 'hidden',
            'shortcut_id' => '',
            'name' => '',
            'galaxy' => '',
            'system' => '',
            'planet' => '',
            'planetTypes' => $this->setPlanetTypes(),
        ]);
    }

    private function editShortcut(): void
    {
        $this->setData();

        $shortcut_id = $this->_clean_data['action'];

        if ($shortcut_id === false) {
            Functions::redirect(self::REDIRECT_TARGET);
        }

        $shortcut = $this->_shortcuts->getById($shortcut_id);

        $this->buildEdit([
            'mode' => 'edit',
            'visibility' => 'button',
            'shortcut_id' => '&a=' . $shortcut_id,
            'name' => $shortcut['name'],
            'galaxy' => $shortcut['g'],
            'system' => $shortcut['s'],
            'planet' => $shortcut['p'],
            'planetTypes' => $this->setPlanetTypes((int) $shortcut['pt']),
        ]);
    }

    private function deleteShortcut(): void
    {
        $this->setData();
    }

    private function buildEdit(array $page): void
    {
        Template::legacyView(
            'fleet.shortcuts.edit',
            $page
        );
    }

    private function setData(): void
    {
        $data = $this->_clean_data['data'];

        if (is_array($data)) {
            if (!empty($data['name']) && $data['galaxy'] && $data['system'] && $data['planet'] && $data['type']) {
                $mode = $this->_clean_data['mode'];
                $action = $this->_clean_data['action'];

                if (!is_null($action) && !is_null($mode)) {
                    if ($mode == 'edit') {
                        $this->_shortcuts->editById(
                            $action,
                            $data['name'],
                            $data['galaxy'],
                            $data['system'],
                            $data['planet'],
                            $data['type']
                        );
                    }

                    if ($mode == 'delete') {
                        $this->_shortcuts->deleteById($action);
                    }
                } else {
                    $this->_shortcuts->addNew(
                        $data['name'],
                        $data['galaxy'],
                        $data['system'],
                        $data['planet'],
                        $data['type']
                    );
                }

                $this->shortcutsModel->updateShortcuts(
                    $this->user['id'],
                    $this->_shortcuts->getAllAsJsonString()
                );
            }

            Functions::redirect(self::REDIRECT_TARGET);
        }
    }

    private function setPlanetTypes(int $selected = 0): array
    {
        $types = [
            PlanetTypesEnumerator::PLANET => 'fl_planet',
            PlanetTypesEnumerator::DEBRIS => 'fl_debris',
            PlanetTypesEnumerator::MOON => 'fl_moon',
        ];
        $options = [];

        foreach ($types as $id => $name) {
            $options[] = [
                'selected' => $id === $selected ? ' selected="selected" ' : '',
                'value' => $id,
                'name' => __('game/fleet.' . $name)
            ];
        }

        return $options;
    }
}
