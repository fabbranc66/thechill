<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../../../core/init.php';

header('Content-Type: application/json; charset=utf-8');

/* ==================================================
   AUTH
================================================== */
if (
  !isset($_SESSION['utente']) ||
  ($_SESSION['utente']['ruolo'] ?? '') !== 'amministratore'
) {
  ob_clean();
  echo json_encode(['ok'=>false,'error'=>'Non autorizzato']);
  exit;
}

/* ==================================================
   INPUT
================================================== */
$id = is_numeric($_POST['id'] ?? null) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
  ob_clean();
  echo json_encode(['ok'=>false,'error'=>'ID non valido']);
  exit;
}

/* ==================================================
   LOGICA ARRIVO
================================================== */
try {
  /* verifica prenotazione */
  $chk = $pdo->prepare("
    SELECT id
    FROM prenotazioni
    WHERE tavolo_id = ?
      AND data = CURDATE()
      AND stato = 'prenotata'
    LIMIT 1
  ");
  $chk->execute([$id]);
  $pren = $chk->fetch();

  if (!$pren) {
    throw new Exception('Prenotazione non valida');
  }

  /* aggiorna prenotazione */
  $upd = $pdo->prepare("
    UPDATE prenotazioni
    SET stato = 'arrivato'
    WHERE id = ?
  ");
  $upd->execute([$pren['id']]);

  if ($upd->rowCount() !== 1) {
    throw new Exception('Aggiornamento fallito');
  }

  ob_clean();
  echo json_encode(['ok'=>true]);
  exit;

} catch (Throwable $e) {
  ob_clean();
  echo json_encode([
    'ok' => false,
    'error' => $e->getMessage()
  ]);
  exit;
}