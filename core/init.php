<?php
declare(strict_types=1);

/* =====================================================
   SESSIONE – valida fino alle 03:00
===================================================== */

if (session_status() === PHP_SESSION_NONE) {

  $now = time();
  $todayAt3 = strtotime('today 03:00');

  // se siamo prima delle 03:00 → scade oggi alle 03:00
  // se siamo dopo → scade domani alle 03:00
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
    'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
  ]);

  session_start();

  // memorizza scadenza assoluta (verità unica)
  if (!isset($_SESSION['EXPIRE_AT'])) {
    $_SESSION['EXPIRE_AT'] = $expireAt;
  }

  // scadenza forzata
  if (time() > $_SESSION['EXPIRE_AT']) {
    session_unset();
    session_destroy();
  }
}

/* =====================================================
   CONFIG DB
===================================================== */
require_once __DIR__ . '/../config/db.php';

/* =====================================================
   BASE URL AUTOMATICA
   - funziona in qualsiasi cartella
   - locale o hosting
===================================================== */
if (!defined('BASE_URL')) {

    // es: /cheersclub/login.php
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

    // es: /cheersclub
    $basePath = str_replace('\\', '/', dirname($scriptName));

    // se siamo dentro /admin, torna alla root progetto
    if (strpos($basePath, '/admin') !== false) {
        $basePath = substr($basePath, 0, strpos($basePath, '/admin'));
    }

    // se siamo in root
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }

    define('BASE_URL', $basePath);
}

/* =====================================================
   BASE URL COMPLETA
===================================================== */
if (!defined('BASE_URL_FULL')) {

    $isHttps =
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

    $scheme = $isHttps ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

    define(
        'BASE_URL_FULL',
        $scheme . '://' . $host . BASE_URL
    );
}

/* =====================================================
   CONNESSIONE PDO
===================================================== */
if (!isset($pdo)) {
  try {
    $pdo = new PDO(
      DB_DSN,
      DB_USER,
      DB_PASS,
      [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
      ]
    );
  } catch (Throwable $e) {
    http_response_code(500);
    exit('Errore connessione database');
  }
}
/* =====================================================
   SETTINGS GLOBALI
===================================================== */
$SETTINGS = [];

$stmt = $pdo->query("SELECT nome, valore FROM settings");
foreach ($stmt as $row) {
  $SETTINGS[$row['nome']] = $row['valore'];
}
/* =====================================================
   UTILS
===================================================== */
if (!function_exists('is_mobile_device')) {
  function is_mobile_device(): bool {
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

    return preg_match(
      '/android|iphone|ipad|ipod|mobile|opera mini|iemobile/',
      $ua
    ) === 1;
  }
}