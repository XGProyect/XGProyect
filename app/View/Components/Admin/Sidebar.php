<?php

declare(strict_types=1);

namespace App\View\Components\Admin;

use App\Models\User;
use App\Services\SettingsService;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Xgp\App\Libraries\Adm\Permissions;

class Sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        private readonly Guard $auth,
        private readonly SettingsService $settingsService,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View | Closure | string
    {
        $activePage = request()->segment(2);

        $sections = [
            'configuration' => [
                'icon' => 'fa-cogs',
                'items' => [
                    'server' => [],
                    'mailing' => [],
                    'modules' => [],
                    'planets' => [],
                    'expeditions' => [],
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
                    'users' => [],
                    'bots' => [],
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
                    'announcement' => [],
                    'ban' => [],
                    'rebuildhighscores' => [],
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

        $authUser = $this->auth->user();
        $role = $authUser instanceof User ? $authUser->authlevel : 0;

        $permissions = new Permissions($this->settingsService->getString('admin_permissions'));

        // Filter each section's items to only those the current role can access
        $sections = collect($sections)
            ->map(function (array $section) use ($permissions, $role) {
                $section['items'] = array_filter(
                    $section['items'],
                    fn (string $item) => $permissions->isAccessAllowed($item, $role),
                    ARRAY_FILTER_USE_KEY
                );
                return $section;
            })
            ->filter(fn (array $section) => !empty($section['items']))
            ->all();

        // determine current block of menus based on the current page
        $activeBlock = collect($sections)
            ->filter(fn ($v) => in_array($activePage, array_keys($v['items'])))
            ->keys()
            ->first();

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
