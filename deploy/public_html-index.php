<?php

/*
 * The web-root entry file for shared hosting where the app lives OUTSIDE the web root (the layout used by
 * .github/workflows/deploy.yml): the app is in ~/td-global and only the contents of /public are served from
 * this site's folder, e.g. ~/public_html/website_d3b60c6c (two levels below the home directory). Copy this file
 * there as index.php ONCE (the deploy never overwrites it).
 * Change $app below if the web root is at a different depth (use '/../td-global' when it is ~/public_html itself)
 * or the app folder is not called "td-global".
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$app = __DIR__.'/../../td-global';

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
