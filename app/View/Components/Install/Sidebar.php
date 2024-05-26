<?php

declare(strict_types=1);

namespace App\View\Components\Install;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;

class Sidebar extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(private Request $request)
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View | Closure | string
    {
        $steps = [
            'requirements' => [
                'icon' => 'fas fa-tasks'
            ],
            'database' => [
                'icon' => 'fas fa-database'
            ],
            'tables' => [
                'icon' => 'fas fa-layer-group'
            ],
            'admin' => [
                'icon' => 'fas fa-user-shield'
            ],
            'final' => [
                'icon' => 'fas fa-check-circle'
            ],
        ];

        return view(
            'components.install.sidebar',
            [
                'steps' => $steps,
                'activeStep' => $this->request->route()->getName(),
            ]
        );
    }
}
