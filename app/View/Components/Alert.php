<?php

declare(strict_types=1);

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Alert extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        private bool $dismissible = true
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View | Closure | string
    {
        $key = '';
        $statusTitle = '';
        $message = '';

        foreach (['success', 'danger', 'warning', 'info'] as $status) {
            if (session()->has($status)) {
                $key = $status;
                $statusTitle = __('admin/global.gn_' . $status . '_title');
                $message = session()->get($status);
                break;
            }
        }

        return view(
            'components.alert',
            [
                'color' => 'alert-' . $key,
                'status' => $statusTitle,
                'message' => $message,
                'dismissible' => (!$this->dismissible ? 'd-none' : ''),
            ]
        );
    }
}
