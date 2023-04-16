<?php

use Xgp\App\Core\common;
use Xgp\App\Libraries\Adm\AdministrationLib;
use Xgp\App\Libraries\Functions;

define('IN_ADMIN', true);
define('XGP_ROOT', base_path('legacy') . DIRECTORY_SEPARATOR);

require XGP_ROOT . 'app' . DIRECTORY_SEPARATOR . 'Core' . DIRECTORY_SEPARATOR . 'Common.php';

$system = new Common();
$system->bootUp('admin');

include_once LIB_PATH . 'Adm' . DIRECTORY_SEPARATOR . 'AdministrationLib.php';

// check updates
$page = filter_input(INPUT_GET, 'page');

if (is_null($page)) {
    $page = 'home';
}

$file_name = ADMIN_PATH . ucfirst($page) . 'Controller.php';

// logout
if ($page == 'logout') {
    AdministrationLib::closeSession();
    Functions::redirect(SYSTEM_ROOT . 'admin.php?page=login');
}

if (file_exists($file_name)) {
    include $file_name;

    $class_name = 'Xgp\App\Http\Controllers\Adm\\' . ucfirst($page) . 'Controller';

    (new $class_name())->__invoke();
} else {
    Functions::redirect(ADM_URL . 'admin.php');
}
