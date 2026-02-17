<?php
session_start();

/*
CONFIGURAZIONE AUTOMATICA:
- Locale → DB locale
- Aruba → DB hosting
*/

$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';

/* CONFIG LOCALE */
if (
    $server_name == "localhost" ||
    $server_name == "127.0.0.1" ||
    strpos($server_name, "192.168.") === 0 ||
    strpos($server_name, "10.") === 0
) {
    $host = "localhost";
    $db   = "sql1874742_4";
    $user = "root";
    $pass = "";
}

/* CONFIG ARUBA */
else {
    $host = '31.11.39.231';
    $db   = 'Sql1874742_4';
    $user = 'Sql1874742';
    $pass = '@GenniH264rgnm';
}

/* CONNESSIONE */
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Errore connessione DB: " . $conn->connect_error);
}

/* charset corretto */
$conn->set_charset("utf8mb4");
?>
