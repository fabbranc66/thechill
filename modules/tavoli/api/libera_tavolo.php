<?php
declare(strict_types=1);

/* =========================================================
   DEBUG (puoi disattivarlo quando vuoi)
========================================================= */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* =========================================================
   BLOCCO OUTPUT (anti JSON.parse)
========================================================= */
ob_start();

/* =========================================================
   INIT
========================================================= */
require __DIR__ . '/../../../core/init.php';

/* =========================================================
   HEADER JSON
========================================================= */
header('Content-Type: application/json; charset=utf-8');

/* =========================================================
   SICUREZZA
========================================================= */
if (
  !isset($_SESSION['utente']) ||
  ($_SESSION['utente']['ruolo'] ?? '') !== 'amministratore'
) {
  ob_clean();
  http_response_code(403);
  echo json_encode([
    'ok'    => false,
    'error' => 'not_authorized'
  ]);
  exit;
}

/* =========================================================
   INPUT (tollerante)
========================================================= */
$tavolo_id = $_POST['tavolo_id'] ?? $_POST['id'] ?? null;
$tavolo_id = is_numeric($tavolo_id) ? (int)$tavolo_id : 0;

if ($tavolo_id <= 0) {
  ob_clean();
  echo json_encode([
    'ok'    => false,
    'error' => 'tavolo_non_valido'
  ]);
  exit;
}

/* =========================================================
   VERIFICA TAVOLO
========================================================= */
$chk = $pdo->prepare(
  "SELECT id
   FROM tavoli
   WHERE id = ? AND attivo = 1"
);
$chk->execute([$tavolo_id]);

if (!$chk->fetch()) {
  ob_clean();
  echo json_encode([
    'ok'    => false,
    'error' => 'tavolo_non_trovato'
  ]);
  exit;
}

/* =========================================================
   LIBERA TAVOLO
   → annulla prenotazioni attive di oggi
========================================================= */
$upd = $pdo->prepare(
  "UPDATE prenotazioni
   SET stato = 'annullata'
   WHERE tavolo_id = ?
     AND data = CURDATE()
     AND stato IN ('prenotata','arrivato')"
);

$upd->execute([$tavolo_id]);

/* =========================================================
   OUTPUT OK
========================================================= */
ob_clean();
echo json_encode([
  'ok'     => true,
  'chiuse' => $upd->rowCount()
]);
exit;