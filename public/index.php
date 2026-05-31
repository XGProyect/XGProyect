<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__.'/../vendor/autoload.php';

/*
 * Subdirectory + Apache rewrite fix.
 *
 * When the user hits /XGProyect/admin/server, Apache rewrites it internally
 * to /XGProyect/public/index.php but leaves SCRIPT_NAME pointing at the
 * rewritten path. Symfony then derives baseUrl as /XGProyect/public/ — wrong
 * for the user's URL — so generated links / redirects include /public/.
 *
 * Apache exposes the original URL via REDIRECT_URL. We use it to rewrite
 * SCRIPT_NAME and PHP_SELF so Symfony detects /XGProyect/ as the base.
 */
if (isset($_SERVER['REDIRECT_URL']) && str_ends_with((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/public/index.php')) {
    $base = substr($_SERVER['SCRIPT_NAME'], 0, -strlen('public/index.php'));
    $_SERVER['SCRIPT_NAME'] = $base . 'index.php';
    $_SERVER['PHP_SELF'] = $base . 'index.php';
}

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
