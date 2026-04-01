<?php

declare(strict_types=1);

use Xgp\App\Core\Common;
use Xgp\App\Libraries\Functions;

define('XGP_ROOT', base_path('legacy') . DIRECTORY_SEPARATOR);

require XGP_ROOT . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Common.php';

$system = new Common();
$system->bootUp('game');

$page = filter_input(INPUT_GET, 'page');

if (empty($page)) {
    Functions::redirect('game.php?page=overview');
}

// kind of a mapping
$page = strtr(
    $page,
    [
        'federationlayer' => 'Federation',
        'shortcuts' => 'Fleetshortcuts',
    ]
);

$file_name = GAME_PATH . ucfirst($page) . 'Controller.php';

// other pages
if (file_exists($file_name)) {
    include $file_name;

    $class_name = 'Xgp\App\Http\Controllers\Game\\' . ucfirst($page) . 'Controller';

    app($class_name)->__invoke();
}

// any other case
Functions::redirect('game.php?page=overview');
