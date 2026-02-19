<?php
declare(strict_types=1);

/* ==========================================================
   ROOT PATH
========================================================== */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
/* =====================================================
   PATH SISTEMA
===================================================== */

define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/upload');

/* ==========================================================
   BASE URL DINAMICA
   indipendente dal nome della root
========================================================== */

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = rtrim(str_replace('/index.php', '', $scriptName), '/');

if (!defined('BASE_URL')) {
    define('BASE_URL', $basePath);
}

$isHttps =
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

$scheme = $isHttps ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (!defined('BASE_URL_FULL')) {
    define('BASE_URL_FULL', $scheme . '://' . $host . BASE_URL);
}


/* =====================================================
   SESSIONE – valida fino alle 03:00
===================================================== */

if (session_status() === PHP_SESSION_NONE) {

  $now = time();
  $todayAt3 = strtotime('today 03:00');

  if ($now < $todayAt3) {
    $expireAt = $todayAt3;
  } else {
    $expireAt = strtotime('tomorrow 03:00');
  }

  $lifetime = $expireAt - $now;

  ini_set('session.gc_maxlifetime', (string)$lifetime);
  ini_set('session.cookie_httponly', '1');
  ini_set('session.use_strict_mode', '1');

  session_set_cookie_params([
    'lifetime' => $lifetime,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
  ]);

  session_start();

  if (!isset($_SESSION['EXPIRE_AT'])) {
    $_SESSION['EXPIRE_AT'] = $expireAt;
  }

  if (time() > $_SESSION['EXPIRE_AT']) {
    session_unset();
    session_destroy();
  }
}

/* =====================================================
   ANTI CACHE GLOBALE
===================================================== */
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


/* =====================================================
   CONNESSIONE DATABASE
   (gestita interamente da config/db.php)
===================================================== */
require ROOT_PATH . '/config/db.php';

/* =====================================================
   MODULE MANAGER
===================================================== */
require ROOT_PATH . '/core/module_manager.php';

$moduleManager = new ModuleManager(
    $pdo,
    ROOT_PATH . '/modules'
);
/* =====================================================
   SETTINGS GLOBALI
===================================================== */
$SETTINGS = [];

$stmt = $pdo->query("SELECT nome, valore FROM settings");
foreach ($stmt as $row) {
  $SETTINGS[$row['nome']] = $row['valore'];
}


/* =====================================================
   AUTH
===================================================== */
require ROOT_PATH . '/core/auth.php';


/* =====================================================
   UTILS
===================================================== */
function is_mobile_device(): bool {
  $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

  return preg_match(
    '/android|iphone|ipad|ipod|mobile|opera mini|iemobile/',
    $ua
  ) === 1;
}
