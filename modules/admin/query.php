<?php
declare(strict_types=1);

/* ==========================================================
   CARICAMENTO MODULI NECESSARI
========================================================== */
require_once __DIR__ . '/../scansioni/query.php';
require_once __DIR__ . '/../carte/query.php';
require_once __DIR__ . '/../clienti/query.php';
require_once __DIR__ . '/../riscatti/query.php';
require_once __DIR__ . '/../gratta/query.php';
require_once ROOT_PATH . '/modules/gratta/query.php';

/* ==========================================================
   STATISTICHE
========================================================== */
$clienti = (int)$pdo->query(
    "SELECT COUNT(*) FROM utenti WHERE ruolo='cliente'"
)->fetchColumn();

$carte = (int)$pdo->query(
    "SELECT COUNT(*) FROM carte_fedelta"
)->fetchColumn();

$punti = (int)$pdo->query(
    "SELECT COALESCE(SUM(punti),0) FROM carte_fedelta"
)->fetchColumn();

$scansioni_totali = (int)$pdo->query(
    "SELECT COUNT(*) FROM log_scansioni"
)->fetchColumn();

$regali_riscattati = (int)$pdo->query(
    "SELECT COUNT(*) FROM riscatti_premi WHERE riscattato=1"
)->fetchColumn();

$gratta_riscattati = (int)$pdo->query(
    "SELECT COUNT(*) FROM gratta_vinci WHERE riscattato=1"
)->fetchColumn();

/* ==========================================================
   FLAG PREMI
========================================================== */
$premi_riscattabili = (bool)$pdo->query(
    "SELECT 1
     FROM carte_fedelta c
     JOIN premi p ON p.attivo=1
     WHERE c.punti >= p.punti_necessari
     LIMIT 1"
)->fetchColumn();

/* ==========================================================
   TAB
========================================================== */
$tab = $_GET['tab'] ?? 'scansioni';

$tab_validi = ['scansioni','carte','clienti','riscatti','gratta'];
if (!in_array($tab, $tab_validi, true)) {
    $tab = 'scansioni';
}

$scansioni = $carte_list = $utenti = $riscatti = $gratta = [];

switch ($tab) {
    case 'scansioni':
        $scansioni = scansioni_lista($pdo);
        break;

    case 'carte':
        $carte_list = carte_lista($pdo);
        break;

    case 'clienti':
        $utenti = clienti_lista($pdo);
        break;

    case 'riscatti':
        $riscatti = riscatti_lista($pdo);
        break;

    case 'gratta':
        $gratta = gratta_lista($pdo);
        break;
}