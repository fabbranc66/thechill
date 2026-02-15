<?php
declare(strict_types=1);

$mod = $_GET['mod'] ?? 'home';

$modulePath = ROOT_PATH . '/modules/' . $mod . '/view.php';

if (!file_exists($modulePath)) {
    http_response_code(404);
    echo "Modulo non trovato";
    exit;
}

require $modulePath;
