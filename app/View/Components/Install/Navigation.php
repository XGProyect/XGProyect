<?php

namespace App\View\Components\Install;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Component;

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
        $disk = Storage::build([
            'driver' => 'local',
            'root' => lang_path(),
        ]);

        return view(
            'components.install.navigation',
            [
                'language' => App::getLocale(),
                'languages' => $disk->directories(),
            ]
        );
    }
}
