<?php

declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/applicazione/Nucleo/Autoload.php';

use Applicazione\Nucleo\Router;

$router = new Router();
$router->avvia();   // ✅ NON dispatch()