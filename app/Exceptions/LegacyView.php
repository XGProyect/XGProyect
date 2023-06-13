<?php

namespace App\Exceptions;

use Exception;
use Illuminate\View\View;

class LegacyView extends Exception
{
    protected $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }
}
