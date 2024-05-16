<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\View\View;

class LegacyView extends Exception
{
    protected View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function getView(): View
    {
        return $this->view;
    }
}
