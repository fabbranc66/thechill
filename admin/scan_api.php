<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

/* ==========================================================
   SICUREZZA
========================================================== */
if (
  !isset($_SESSION['utente']) ||
  $_SESSION['utente']['ruolo'] !== 'amministratore'
) {
  echo json_encode([
    'ok' => false,
    'error' => 'not_authorized'
  ]);
  exit;
}

/* ==========================================================
   INPUT
========================================================== */
$qr = trim($_POST['qr'] ?? '');
if ($qr === '') {
  echo json_encode([
    'ok' => false,
    'error' => 'qr_mancante'
  ]);
  exit;
}

/* ==========================================================
   RECUPERO CARTA + TELEFONO
========================================================== */
$stmt = $pdo->prepare(
  "SELECT c.id, c.punti, u.telefono
   FROM carte_fedelta c
   JOIN utenti u ON u.id = c.utente_id
   WHERE c.codice_carta = ?
   LIMIT 1"
);
$stmt->execute([$qr]);
$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
  echo json_encode([
    'ok' => false,
    'error' => 'carta_non_trovata'
  ]);
  exit;
}

$carta_id = (int)$carta['id'];
$telefonoRaw = trim((string)$carta['telefono']);

/* ==========================================================
   TRANSAZIONE
========================================================== */
try {
  $pdo->beginTransaction();

  /* incremento punti */
  $pdo->prepare(
    "UPDATE carte_fedelta
     SET punti = punti + 1
     WHERE id = ?"
  )->execute([$carta_id]);

  /* log scansione */
  $pdo->prepare(
    "INSERT INTO log_scansioni (carta_id, punti, origine)
     VALUES (?, 1, 'qr')"
  )->execute([$carta_id]);

  /* totale aggiornato */
  $totale = (int)$pdo->query(
    "SELECT punti FROM carte_fedelta WHERE id = $carta_id"
  )->fetchColumn();

  /* ======================================================
     PREMI CLASSICI
  ====================================================== */
  $stmt = $pdo->prepare(
    "SELECT nome, punti_necessari
     FROM premi
     WHERE attivo = 1
       AND punti_necessari <= ?
     ORDER BY punti_necessari ASC"
  );
  $stmt->execute([$totale]);
  $premi = $stmt->fetchAll(PDO::FETCH_ASSOC);

  /* ======================================================
     CREAZIONE GRATTA E VINCI
  ====================================================== */
  $gratta = null;

  $cfg = $pdo->query(
    "SELECT nome,valore FROM settings
     WHERE nome IN (
       'gratta_attivo',
       'gratta_probabilita',
       'gratta_premio_punti'
     )"
  )->fetchAll(PDO::FETCH_KEY_PAIR);

  $attivo = ($cfg['gratta_attivo'] ?? '1') === '1';

  if ($attivo) {

    $n = max(1, (int)($cfg['gratta_probabilita'] ?? 5));
    $premio = max(0, (int)($cfg['gratta_premio_punti'] ?? 10));
    $vincente = (random_int(1, $n) === 1);

    $token = bin2hex(random_bytes(32));

    $pdo->prepare(
      "INSERT INTO gratta_vinci
       (carta_id, token, premio_punti, vincente)
       VALUES (?, ?, ?, ?)"
    )->execute([
      $carta_id,
      $token,
      $premio,
      (int)$vincente
    ]);

    /* prepara link e WhatsApp */
    $grattaLink = BASE_URL_FULL . '/gratta.php?token=' . $token;

    $waUrl = null;
    if ($telefonoRaw !== '') {
      $telefono = preg_replace('/[^0-9]/', '', $telefonoRaw);
      if (strlen($telefono) >= 9) {
        if (!str_starts_with($telefono, '39')) {
          $telefono = '39' . $telefono;
        }
        $msg = "🎁 Hai ricevuto un gratta e vinci!\n\nGratta qui 👉 $grattaLink";
        $waUrl = 'https://wa.me/' . $telefono . '?text=' . urlencode($msg);
      }
    }

    $gratta = [
      'token' => $token,
      'link'  => $grattaLink,
      'wa'    => $waUrl,
      'vincente' => $vincente,
      'premio'   => $premio
    ];
  }

  $pdo->commit();

  echo json_encode([
    'ok' => true,<?php
// ==========================================================
// FILE: admin/scan_api.php
// RUOLO:
// - endpoint JSON per scansione QR
// - incrementa punti
// - restituisce premi disponibili
// - genera eventuale gratta e vinci
// ==========================================================

declare(strict_types=1);


// ==========================================================
// 1. DEBUG / ERROR REPORTING
// Attivo solo in ambiente di sviluppo
// ==========================================================

ini_set('display_errors', '1');
error_reporting(E_ALL);


// ==========================================================
// 2. BOOTSTRAP APPLICAZIONE
// Carica configurazione, sessione e PDO
// ==========================================================

require __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');


// ==========================================================
// 3. SICUREZZA
// Accesso consentito solo ad amministratori autenticati
// ==========================================================

if (
    !isset($_SESSION['utente']) ||
    ($_SESSION['utente']['ruolo'] ?? '') !== 'amministratore'
) {
    echo json_encode([
        'ok'    => false,
        'error' => 'not_authorized'
    ]);
    exit;
}


// ==========================================================
// 4. INPUT
// Recupero e validazione codice QR
// ==========================================================

$qr = trim($_POST['qr'] ?? '');
if ($qr === '') {
    echo json_encode([
        'ok'    => false,
        'error' => 'qr_mancante'
    ]);
    exit;
}


// ==========================================================
// 5. RECUPERO CARTA + TELEFONO UTENTE
// ==========================================================

$stmt = $pdo->prepare(
    "SELECT c.id, c.punti, u.telefono
     FROM carte_fedelta c
     JOIN utenti u ON u.id = c.utente_id
     WHERE c.codice_carta = ?
     LIMIT 1"
);
$stmt->execute([$qr]);

$carta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carta) {
    echo json_encode([
        'ok'    => false,
        'error' => 'carta_non_trovata'
    ]);
    exit;
}

$carta_id    = (int) $carta['id'];
$telefonoRaw = trim((string) $carta['telefono']);


// ==========================================================
// 6. TRANSAZIONE DATABASE
// - incremento punti
// - log scansione
// - calcolo premi
// - eventuale gratta e vinci
// ==========================================================

try {
    $pdo->beginTransaction();

    // ------------------------------------------------------
    // 6.1 INCREMENTO PUNTI CARTA
    // ------------------------------------------------------

    $pdo->prepare(
        "UPDATE carte_fedelta
         SET punti = punti + 1
         WHERE id = ?"
    )->execute([$carta_id]);

    // ------------------------------------------------------
    // 6.2 LOG SCANSIONE
    // ------------------------------------------------------

    $pdo->prepare(
        "INSERT INTO log_scansioni (carta_id, punti, origine)
         VALUES (?, 1, 'qr')"
    )->execute([$carta_id]);

    // ------------------------------------------------------
    // 6.3 TOTALE PUNTI AGGIORNATO
    // ------------------------------------------------------

    $totale = (int) $pdo->query(
        "SELECT punti FROM carte_fedelta WHERE id = $carta_id"
    )->fetchColumn();

    // ------------------------------------------------------
    // 6.4 PREMI CLASSICI DISPONIBILI
    // ------------------------------------------------------

    $stmt = $pdo->prepare(
        "SELECT nome, punti_necessari
         FROM premi
         WHERE attivo = 1
           AND punti_necessari <= ?
         ORDER BY punti_necessari ASC"
    );
    $stmt->execute([$totale]);

    $premi = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ------------------------------------------------------
    // 6.5 CONFIGURAZIONE GRATTA E VINCI
    // ------------------------------------------------------

    $gratta = null;

    $cfg = $pdo->query(
        "SELECT nome, valore
         FROM settings
         WHERE nome IN (
            'gratta_attivo',
            'gratta_probabilita',
            'gratta_premio_punti'
         )"
    )->fetchAll(PDO::FETCH_KEY_PAIR);

    $attivo = ($cfg['gratta_attivo'] ?? '1') === '1';

    // ------------------------------------------------------
    // 6.6 CREAZIONE GRATTA E VINCI
    // ------------------------------------------------------

    if ($attivo) {

        $n        = max(1, (int) ($cfg['gratta_probabilita'] ?? 5));
        $premio  = max(0, (int) ($cfg['gratta_premio_punti'] ?? 10));
        $vincente = (random_int(1, $n) === 1);

        $token = bin2hex(random_bytes(32));

        $pdo->prepare(
            "INSERT INTO gratta_vinci
             (carta_id, token, premio_punti, vincente)
             VALUES (?, ?, ?, ?)"
        )->execute([
            $carta_id,
            $token,
            $premio,
            (int) $vincente
        ]);

        // --------------------------------------------------
        // 6.7 LINK GRATTA + WHATSAPP
        // --------------------------------------------------

        $grattaLink = BASE_URL_FULL . '/gratta.php?token=' . $token;

        $waUrl = null;
        if ($telefonoRaw !== '') {
            $telefono = preg_replace('/[^0-9]/', '', $telefonoRaw);
            if (strlen($telefono) >= 9) {
                if (!str_starts_with($telefono, '39')) {
                    $telefono = '39' . $telefono;
                }
                $msg  = "🎁 Hai ricevuto un gratta e vinci!\n\nGratta qui 👉 $grattaLink";
                $waUrl = 'https://wa.me/' . $telefono . '?text=' . urlencode($msg);
            }
        }

        $gratta = [
            'token'    => $token,
            'link'     => $grattaLink,
            'wa'       => $waUrl,
            'vincente' => $vincente,
            'premio'   => $premio
        ];
    }

    $pdo->commit();

    // ======================================================
    // 7. RISPOSTA JSON OK
    // ======================================================

    echo json_encode([
        'ok'             => true,
        'punti_aggiunti' => 1,
        'punti_totali'   => $totale,
        'premi'          => $premi,
        'gratta'         => $gratta
    ]);
    exit;

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // ======================================================
    // 8. RISPOSTA JSON ERRORE
    // ======================================================

    echo json_encode([
        'ok'    => false,
        'error' => 'db_error'
    ]);
    exit;
}

    'punti_aggiunti' => 1,
    'punti_totali'   => $totale,
    'premi'          => $premi,
    'gratta'         => $gratta
  ]);
  exit;

} catch (Throwable $e) {
  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  echo json_encode([
    'ok' => false,
    'error' => 'db_error'
  ]);
  exit;
}