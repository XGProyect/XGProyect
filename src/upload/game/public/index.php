<?php

use App\core\common;
use App\libraries\Functions;

define('XGP_ROOT', realpath(dirname(__DIR__)) . DIRECTORY_SEPARATOR);

require XGP_ROOT . 'app' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'common.php';

$system = new Common;
$system->bootUp('game');

$page = filter_input(INPUT_GET, 'page');

if (is_null($page)) {
    Functions::redirect('index.php?page=overview');
}

// kind of a mapping
$page = strtr(
    $page,
    [
        'resources' => 'buildings',
        'resourceSettings' => 'resources',
        'station' => 'buildings',
        'federationlayer' => 'federation',
        'shortcuts' => 'fleetshortcuts',
        'forums' => 'forum',
        'defense' => 'shipyard',
    ]
);

$file_name = XGP_ROOT . CONTROLLERS_PATH . $page . '.php';

if (isset($page)) {
    // other pages
    if (file_exists($file_name)) {
        include $file_name;

        $class_name = 'App\controllers\\' . ucfirst($page);

        (new $class_name)->index();
    }
}

// any other case
Functions::redirect('index.php?page=overview');
