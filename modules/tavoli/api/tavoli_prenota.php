<?php
declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/* =========================================
   BLOCCO OUTPUT
========================================= */
ob_start();

/* =========================================
   INIT
========================================= */
require __DIR__ . '/../../../core/init.php';

/* =========================================
   HEADER JSON
========================================= */
header('Content-Type: application/json; charset=utf-8');

/* =========================================
   AUTH
========================================= */
if (
  !isset($_SESSION['utente']) ||
  !in_array(($_SESSION['utente']['ruolo'] ?? ''), ['amministratore','operatore'], true)
) {
  ob_clean();
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'non_autorizzato']);
  exit;
}

/* =========================================
   INPUT
========================================= */
/* =========================================
   INPUT (tollerante)
========================================= */
/* =========================================
   INPUT (ULTRA ROBUSTO)
========================================= */
$tavolo_id = $_POST['tavolo_id'] ?? $_POST['id'] ?? null;
$tavolo_id = is_numeric($tavolo_id) ? (int)$tavolo_id : 0;

/* nome_cliente: opzionale ma garantito */
$nome_cliente = trim($_POST['nome_cliente'] ?? $_POST['nome'] ?? '');
if ($nome_cliente === '') {
  $nome_cliente = 'Cliente';
}

/* ora_inizio: se manca, ora attuale */
$ora_inizio = trim($_POST['ora_inizio'] ?? $_POST['ora'] ?? '');
if ($ora_inizio === '') {
  $ora_inizio = date('H:i:s');
}

/* unica validazione reale */
if ($tavolo_id <= 0) {
  ob_clean();
  echo json_encode([
    'ok' => false,
    'error' => 'dati_non_validi'
  ]);
  exit;
}
/* =========================================
   VERIFICA TAVOLO
========================================= */
$stmt = $pdo->prepare(
  "SELECT id FROM tavoli WHERE id = ? AND attivo = 1"
);
$stmt->execute([$tavolo_id]);

if (!$stmt->fetch()) {
  ob_clean();
  echo json_encode(['ok'=>false,'error'=>'tavolo_non_esistente']);
  exit;
}

/* =========================================
   VERIFICA DISPONIBILITÀ
========================================= */
$stmt = $pdo->prepare(
  "SELECT COUNT(*) FROM prenotazioni
   WHERE tavolo_id = ?
     AND data = CURDATE()
     AND stato IN ('prenotata','arrivato')"
);
$stmt->execute([$tavolo_id]);

if ((int)$stmt->fetchColumn() > 0) {
  ob_clean();
  echo json_encode(['ok'=>false,'error'=>'tavolo_occupato']);
  exit;
}

/* =========================================
   CREAZIONE PRENOTAZIONE
========================================= */
$codice = bin2hex(random_bytes(8));

$stmt = $pdo->prepare(
  "INSERT INTO prenotazioni
   (tavolo_id, codice_accesso, nome_cliente, data, ora_inizio, ora_fine, stato)
   VALUES (?, ?, ?, CURDATE(), ?, ADDTIME(?, '02:00:00'), 'prenotata')"
);

$stmt->execute([
  $tavolo_id,
  $codice,
  $nome_cliente,
  $ora_inizio,
  $ora_inizio
]);

/* =========================================
   OUTPUT OK
========================================= */
ob_clean();
echo json_encode(['ok'=>true,'codice'=>$codice]);
exit;