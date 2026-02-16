<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);
ob_start();

/* ==========================================================
   INIT
========================================================== */
require __DIR__ . '/../../../core/init.php';

header('Content-Type: application/json; charset=utf-8');

/* ==========================================================
   SICUREZZA
========================================================== */
if (
  !isset($_SESSION['utente']) ||
  ($_SESSION['utente']['ruolo'] ?? '') !== 'amministratore'
) {
  ob_clean();
  http_response_code(403);
  echo json_encode([
    'ok' => false,
    'error' => 'Non autorizzato'
  ]);
  exit;
}

/* ==========================================================
   MAPPA DA SETTINGS
========================================================== */
$mappa = [];
if (!empty($SETTINGS['tavoli_mappa'])) {
  $decoded = json_decode($SETTINGS['tavoli_mappa'], true);
  if (is_array($decoded)) {
    $mappa = $decoded;
  }
}

/* se mappa vuota → fallback a primi 20 tavoli */
if (empty($mappa)) {
  for ($i = 1; $i <= 20; $i++) {
    $mappa[$i] = ['x' => ($i-1)%5, 'y' => floor(($i-1)/5)];
  }
}

$ids = array_map('intval', array_keys($mappa));
$idList = implode(',', $ids);

if ($idList === '') {
  ob_clean();
  echo json_encode(['ok' => true, 'tavoli' => []]);
  exit;
}

/* ==========================================================
   QUERY SOLO TAVOLI PRESENTI IN MAPPA
========================================================== */
$sql = "
SELECT
  t.id,
  t.nome,
  t.posti,
  t.attivo,

  CASE
    WHEN EXISTS (
      SELECT 1
      FROM prenotazioni p
      WHERE p.tavolo_id = t.id
        AND p.data = CURDATE()
        AND p.stato = 'arrivato'
    ) THEN 'occupato'

    WHEN EXISTS (
      SELECT 1
      FROM prenotazioni p
      WHERE p.tavolo_id = t.id
        AND p.data = CURDATE()
        AND p.stato = 'prenotata'
    ) THEN 'prenotato'

    ELSE 'libero'
  END AS stato

FROM tavoli t
WHERE t.attivo = 1
AND t.id IN ($idList)
";

try {
  $rows = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

  /* indicizza per id */
  $byId = [];
  foreach ($rows as $r) {
    $byId[$r['id']] = $r;
  }

  /* ricostruisce lista secondo mappa */
  $tavoli = [];
  foreach ($mappa as $id => $pos) {
    if (isset($byId[$id])) {
      $t = $byId[$id];
      $t['x'] = $pos['x'];
      $t['y'] = $pos['y'];
      $tavoli[] = $t;
    }
  }

  ob_clean();
  echo json_encode([
    'ok'     => true,
    'tavoli' => $tavoli
  ]);
  exit;

} catch (Throwable $e) {
  ob_clean();
  echo json_encode([
    'ok'    => false,
    'error' => $e->getMessage()
  ]);
  exit;
}
