<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../../../core/init.php';

header('Content-Type: application/json; charset=utf-8');

/* ==========================================================
   CARICA MAPPA DA SETTINGS
========================================================== */
$mappa = [];
if (!empty($SETTINGS['tavoli_mappa'])) {
    $tmp = json_decode($SETTINGS['tavoli_mappa'], true);
    if (is_array($tmp)) {
        $mappa = $tmp;
    }
}

/* ==========================================================
   QUERY TAVOLI BASE
========================================================== */
$sql = "
SELECT
  t.id,
  t.nome,
  t.posti,
  CASE
    WHEN EXISTS (
      SELECT 1
      FROM prenotazioni p
      WHERE p.tavolo_id = t.id
        AND p.data = CURDATE()
        AND p.stato IN ('prenotata','arrivato')
    ) THEN 'occupato'
    ELSE 'libero'
  END AS stato
FROM tavoli t
WHERE t.attivo = 1
ORDER BY t.id
";

$tutti = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

/* ==========================================================
   FILTRA E POSIZIONA SECONDO MAPPA
========================================================== */
$tavoli = [];

foreach ($tutti as $t) {
    $id = (string)$t['id'];

    if (!isset($mappa[$id])) {
        continue;
    }

    $t['x'] = (int)$mappa[$id]['x'];
    $t['y'] = (int)$mappa[$id]['y'];

    $tavoli[] = $t;
}

ob_clean();
echo json_encode([
  'ok'     => true,
  'tavoli' => $tavoli
]);
exit;
