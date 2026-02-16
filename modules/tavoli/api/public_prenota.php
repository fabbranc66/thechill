<?php
declare(strict_types=1);

ini_set('display_errors', 1);
error_reporting(E_ALL);
ob_start();

require __DIR__ . '/../../../core/init.php';

header('Content-Type: application/json; charset=utf-8');

/* ==========================================================
   INPUT
========================================================== */
$tavoli_raw = $_POST['tavoli'] ?? '';
$nome_cliente = trim($_POST['nome_cliente'] ?? '');
$ora_inizio = trim($_POST['ora'] ?? '');
$persone = (int)($_POST['persone'] ?? 1);
$token = trim($_POST['token'] ?? '');

if ($ora_inizio === '') {
    $ora_inizio = date('H:i:s');
}

if ($tavoli_raw === '' || $nome_cliente === '' || $persone <= 0) {
    ob_clean();
    echo json_encode(['ok'=>false,'error'=>'dati_non_validi']);
    exit;
}

/* ==========================================================
   PARSE TAVOLI
========================================================== */
$tavoli = array_filter(
    array_map('intval', explode(',', $tavoli_raw)),
    fn($v) => $v > 0
);

if (empty($tavoli)) {
    ob_clean();
    echo json_encode(['ok'=>false,'error'=>'tavoli_non_validi']);
    exit;
}

/* ==========================================================
   RICAVA UTENTE DAL TOKEN (se presente)
========================================================== */
$utente_id = null;

if ($token !== '') {
    $stmt = $pdo->prepare("
        SELECT id
        FROM utenti
        WHERE token_accesso = ?
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $utente_id = $stmt->fetchColumn();

    if ($utente_id) {
        $utente_id = (int)$utente_id;
    } else {
        $utente_id = null;
    }
}

/* ==========================================================
   VERIFICA OCCUPAZIONE
========================================================== */
$sql = "
SELECT tavolo_id
FROM prenotazioni
WHERE data = CURDATE()
AND stato IN ('prenotata','arrivato')
";
$occ = $pdo->query($sql)->fetchAll(PDO::FETCH_COLUMN);
$occupati = array_map('intval', $occ);

foreach ($tavoli as $tid) {
    if (in_array($tid, $occupati, true)) {
        ob_clean();
        echo json_encode([
            'ok'=>false,
            'error'=>'tavolo_occupato'
        ]);
        exit;
    }
}

/* ==========================================================
   CREAZIONE PRENOTAZIONI
========================================================== */
$codice = bin2hex(random_bytes(8));

$stmt = $pdo->prepare("
INSERT INTO prenotazioni
(
  tavolo_id,
  utente_id,
  codice_accesso,
  nome_cliente,
  data,
  ora_inizio,
  ora_fine,
  stato
)
VALUES (?, ?, ?, ?, CURDATE(), ?, ADDTIME(?, '02:00:00'), 'prenotata')
");

foreach ($tavoli as $tid) {
    $stmt->execute([
        $tid,
        $utente_id,
        $codice,
        $nome_cliente,
        $ora_inizio,
        $ora_inizio
    ]);
}

/* ==========================================================
   OUTPUT
========================================================== */
ob_clean();
echo json_encode([
    'ok' => true,
    'utente_id' => $utente_id,
    'tavoli' => $tavoli
]);
exit;
