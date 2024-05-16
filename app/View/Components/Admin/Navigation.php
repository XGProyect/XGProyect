<?php

declare(strict_types=1);

namespace App\View\Components\Admin;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Xgp\App\Libraries\TimingLibrary;

class Navigation extends Component
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
        return view(
            'components.admin.navigation',
            [
                'username' => User::find(session('user_id'))->name,
                'currentDate' => TimingLibrary::formatShortDate(time()),
            ]
        );
    }
}
