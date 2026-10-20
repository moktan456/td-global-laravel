<?php

/*
 * The web-root entry file for shared hosting where the app lives OUTSIDE public_html (the layout used by
 * .github/workflows/deploy.yml): the app is in ~/td-global and only the contents of /public are served from
 * ~/public_html. Copy this file to ~/public_html/index.php ONCE (the deploy never overwrites it).
 * If your app folder is not called "td-global", change $app below.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$app = __DIR__.'/../td-global';

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $app.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $app.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $laravel */
$laravel = require_once $app.'/bootstrap/app.php';

$laravel->handleRequest(Request::capture());
