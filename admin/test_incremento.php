<?php
declare(strict_types=1);

/* ==========================================================
   DEBUG ON
========================================================== */
ini_set('display_errors', '1');
error_reporting(E_ALL);

/* ==========================================================
   BOOTSTRAP
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../modules/carte/actions.php';

/* ==========================================================
   INPUT TOKEN
   usa:
   test_incremento.php?t=TOKEN
========================================================== */
$token = $_GET['t'] ?? '';

if ($token === '') {
    exit('Token mancante');
}

/* ==========================================================
   TEST INCREMENTO
========================================================== */
$punti = 1;

echo "<pre>";
echo "TOKEN: $token\n";

$ok = carte_incrementa_punti($pdo, $token, $punti, 'test');

if ($ok) {
    echo "✔ Incremento OK\n";
} else {
    echo "✖ Incremento FALLITO\n";
}

/* ==========================================================
   VERIFICA PUNTI ATTUALI
========================================================== */
$stmt = $pdo->prepare(
    "SELECT c.id, c.punti, u.nome
     FROM carte_fedelta c
     JOIN utenti u ON u.id = c.utente_id
     WHERE u.token_accesso = ?
     LIMIT 1"
);
$stmt->execute([$token]);

$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if ($carta) {
    echo "\nCarta ID: " . $carta['id'];
    echo "\nCliente: " . $carta['nome'];
    echo "\nPunti attuali: " . $carta['punti'];
} else {
    echo "\nCarta non trovata per questo token";
}

echo "</pre>";
