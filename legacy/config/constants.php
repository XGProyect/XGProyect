<?php

//##########################################################################
//
// Constants should not be changed, unless you know what you are doing!
//
//##########################################################################

/**
 *
 * SYSTEM PATHS CONFIGURATION
 *
 */
if ((!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ||
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
    (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443')) {
    define('PROTOCOL', 'https://');
} else {
    define('PROTOCOL', 'http://');
}

// BASE PATH
define(
    'BASE_PATH',
    $_SERVER['HTTP_HOST'] . str_replace(
        '/' . basename($_SERVER['SCRIPT_NAME']),
        '',
        $_SERVER['SCRIPT_NAME']
    )
);

// SYSTEM ROOT, IGNORING PUBLIC
define('SYSTEM_ROOT', PROTOCOL . strtr(BASE_PATH, ['public' => '', 'public/' => '']) . '/');

// ADMIN PATHS
define('ADM_URL', PROTOCOL . strtr(BASE_PATH, ['public' => '', 'public/' => '']) . '/');

/**
 *
 * GLOBAL DIRECTORY STRUCTURE
 *
 */
define('APP_PATH', XGP_ROOT . 'app' . DIRECTORY_SEPARATOR);
define('CONFIGS_PATH', config_path() . DIRECTORY_SEPARATOR);
define('PUBLIC_PATH', XGP_ROOT . 'public' . DIRECTORY_SEPARATOR);

/**
 *
 * APPLICATION DIRECTORY STRUCTURE
 *
 */
define('CONTROLLERS_PATH', APP_PATH . 'Http' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR);
define('CORE_PATH', APP_PATH . 'Core' . DIRECTORY_SEPARATOR);
define('LIB_PATH', APP_PATH . 'Libraries' . DIRECTORY_SEPARATOR);

/**
 *
 * CONTROLLERS DIRECTORY STRUCTURE
 *
 */
define('ADMIN_PATH', CONTROLLERS_PATH . 'Adm' . DIRECTORY_SEPARATOR);
define('GAME_PATH', CONTROLLERS_PATH . 'Game' . DIRECTORY_SEPARATOR);
define('INSTALL_PATH', CONTROLLERS_PATH . 'Install' . DIRECTORY_SEPARATOR);

/**
 *
 * PUBLIC DIRECTORY STRUCTURE
 *
 */
define('IMG_PATH', 'assets/images' . DIRECTORY_SEPARATOR);
define('UPLOAD_PATH', 'assets/upload' . DIRECTORY_SEPARATOR);

/**
 *
 * SKIN DIRECTORY STRUCTURE
 *
 */
define('SKIN_PATH', UPLOAD_PATH . 'skins' . DIRECTORY_SEPARATOR);
define('DEFAULT_SKINPATH', SKIN_PATH . 'xgproyect' . DIRECTORY_SEPARATOR);
define('DPATH', DEFAULT_SKINPATH);

define('MB_ENABLED', false);
define('ICONV_ENABLED', false);

ini_set('default_charset', 'UTF-8');
