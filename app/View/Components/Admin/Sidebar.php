<?php

declare(strict_types=1);

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $activePage = isset($_GET['page']) ? $_GET['page'] : null;

        $sections = [
            'configuration' => [
                'icon' => 'fa-cogs',
                'items' => [
                    'server' => [],
                    'mailing' => [],
                    'modules' => [],
                    'planets' => [],
                    'registration' => [],
                    'statistics' => [],
                    'premium' => [],
                ],
            ],
            'information' => [
                'icon' => 'fa-info-circle',
                'items' => [
                    'tasks' => [],
                    'errors' => [],
                    'fleets' => [],
                    'messages' => [],
                ]
            ],
            'edition' => [
                'icon' => 'fa-pen',
                'items' => [
                    'maker' => [],
                    'users' => [],
                    'alliances' => [],
                    'languages' => [],
                    'changelog' => [],
                    'permissions' => [],
                ],
            ],
            'tools' => [
                'icon' => 'fa-tools',
                'items' => [
                    'backup' => [],
                    'encrypter' => [],
                    'announcement' => [],
                    'ban' => [],
                    'rebuildhighscores' => ['onClick="return confirm(\'' . __('admin/menu.tools_manual_update_confirm') . '\');"'],
                    'update' => [],
                ],
            ],
            'maintenance' => [
                'icon' => 'fa-brush',
                'items' => [
                    'repair' => [],
                    'reset' => [],
                ],
            ],
        ];

        // determine current block of menus based on the current page
        $activeBlock = collect($sections)->filter(fn ($v) => in_array($activePage, array_keys($v['items'])))->keys()->first();

        return view(
            'components.admin.sidebar',
            [
                'sections' => $sections,
                'activePage' => $activePage,
                'activeBlock' => $activeBlock ?? '',
            ]
        );
    }
}
