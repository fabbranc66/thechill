<?php
declare(strict_types=1);

/* =====================================================
   RILEVAMENTO AMBIENTE
===================================================== */
$rawHost = (string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
$rawHost = trim($rawHost);

// Rimuove la porta solo per host classici (es. localhost:8080)
if (strpos($rawHost, ':') !== false && substr_count($rawHost, ':') === 1) {
    [$rawHost] = explode(':', $rawHost, 2);
}

$host = strtolower($rawHost);

$isLocalhost = in_array($host, [
    'localhost',
    '127.0.0.1',
    '::1',
], true);

// Se eseguito da CLI senza host, trattalo come locale.
if ($host === '' && PHP_SAPI === 'cli') {
    $isLocalhost = true;
}

/* =====================================================
   RILEVA AMBIENTE
===================================================== */
$host = $_SERVER['HTTP_HOST'] ?? '';

$isLocalhost =
    $host === 'localhost' ||
    $host === '127.0.0.1' ||
    $host === '192.168.1.20';

/* =====================================================
   CONFIGURAZIONE DATABASE
===================================================== */
if ($isLocalhost) {

    /* ===== LOCALE (XAMPP) ===== */
    $db_host    = '127.0.0.1';
    $db_name    = 'Sql1874742_5';
    $db_user    = 'root';
    $db_pass    = '';
    $db_charset = 'utf8mb4';

} else {

    /* ===== PRODUZIONE ===== */
    $db_host    = '31.11.39.231';
    $db_name    = 'Sql1874742_5';
    $db_user    = 'Sql1874742';
    $db_pass    = '@GenniH264rgnm';
    $db_charset = 'utf8mb4';
}
/* =====================================================
   CONFIGURAZIONE DATABASE
===================================================== */
if ($isLocalhost) {

    /* ===== LOCALE (XAMPP) ===== */
    $db_host    = '127.0.0.1';
    $db_name    = 'Sql1874742_5';
    $db_user    = 'root';
    $db_pass    = '';
    $db_charset = 'utf8mb4';

} else {

    /* ===== PRODUZIONE ===== */
    $db_host    = '31.11.39.231';
    $db_name    = 'Sql1874742_5';
    $db_user    = 'Sql1874742';
    $db_pass    = '@GenniH264rgnm';
    $db_charset = 'utf8mb4';
}

/* =====================================================
   OVERRIDE DA ENV (OPZIONALE)
===================================================== */
$db_host    = (string)(getenv('DB_HOST') ?: $db_host);
$db_name    = (string)(getenv('DB_NAME') ?: $db_name);
$db_user    = (string)(getenv('DB_USER') ?: $db_user);
$db_pass    = (string)(getenv('DB_PASS') ?: $db_pass);
$db_charset = (string)(getenv('DB_CHARSET') ?: $db_charset);

if ($db_host === '' || $db_name === '' || $db_user === '') {
    http_response_code(500);
    exit('Configurazione database incompleta.');
}

/* =====================================================
   CONNESSIONE PDO
===================================================== */
$dsn = "mysql:host={$db_host};dbname={$db_name};charset={$db_charset}";

try {

    $pdo = new PDO(
        $dsn,
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ]
    );

} catch (PDOException $e) {

    http_response_code(500);

    if ($isLocalhost) {
        exit('❌ ERRORE DB LOCALE: ' . $e->getMessage());
    }

    exit('❌ Errore di connessione al database.');
}
