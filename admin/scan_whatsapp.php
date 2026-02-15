<?php
declare(strict_types=1);

/* ==========================================================
   FILE: admin/scan_whatsapp.php

   SCOPO:
   - scansione QR carta fedeltà
   - incremento punti carta
   - log scansione
   - eventuale creazione gratta e vinci
   - redirect automatico WhatsApp al cliente

   NOTE:
   - accesso consentito SOLO ad amministratore
   - usa redirect wa.me (no API esterne)
========================================================== */


/* ==========================================================
   BLOCCO 1 — DEBUG (SOLO SVILUPPO)
   In produzione puoi commentare questo blocco
========================================================== */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


/* ==========================================================
   BLOCCO 2 — BOOTSTRAP + SICUREZZA
   - inizializza sessione e PDO
   - verifica ruolo amministratore
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

richiedi_ruolo('amministratore');


/* ==========================================================
   BLOCCO 3 — LETTURA INPUT QR
   - recupera codice carta dal QR
========================================================== */
$qr = trim($_GET['qr'] ?? '');

if ($qr === '') {
  exit('QR non valido');
}


/* ==========================================================
   BLOCCO 4 — RECUPERO CARTA FEDELTÀ + TELEFONO
   - associa carta → utente → telefono
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
  exit('Carta non trovata');
}

$carta_id    = (int) $carta['id'];
$telefonoRaw = trim((string) $carta['telefono']);


/* ==========================================================
   BLOCCO 5 — TRANSAZIONE DATABASE
   Include:
   - incremento punti
   - log scansione
   - lettura impostazioni gratta e vinci
   - eventuale creazione gratta
========================================================== */
try {

  $pdo->beginTransaction();

  /* --------------------------------------------------------
     5.1 Incremento punti carta
  -------------------------------------------------------- */
  $pdo->prepare(
    "UPDATE carte_fedelta
     SET punti = punti + 1
     WHERE id = ?"
  )->execute([$carta_id]);

  /* --------------------------------------------------------
     5.2 Log scansione QR
  -------------------------------------------------------- */
  $pdo->prepare(
    "INSERT INTO log_scansioni (carta_id, punti, origine)
     VALUES (?, 1, 'qr')"
  )->execute([$carta_id]);

  /* --------------------------------------------------------
     5.3 Lettura configurazione gratta e vinci
  -------------------------------------------------------- */
  $cfg = $pdo->query(
    "SELECT nome, valore
     FROM impostazioni
     WHERE nome IN (
       'gratta_attivo',
       'gratta_probabilita',
       'gratta_premio_punti'
     )"
  )->fetchAll(PDO::FETCH_KEY_PAIR);

  $grattaAttivo = ($cfg['gratta_attivo'] ?? '1') === '1';
  $token = null;

  /* --------------------------------------------------------
     5.4 Creazione gratta e vinci (se attivo)
  -------------------------------------------------------- */
  if ($grattaAttivo) {

    $probabilita = max(1, (int) ($cfg['gratta_probabilita'] ?? 5));
    $premio      = max(0, (int) ($cfg['gratta_premio_punti'] ?? 10));
    $vincente    = (random_int(1, $probabilita) === 1);

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
  }

  $pdo->commit();

} catch (Throwable $e) {

  if ($pdo->inTransaction()) {
    $pdo->rollBack();
  }

  http_response_code(500);
  exit('Errore incremento punti');
}


/* ==========================================================
   BLOCCO 6 — REDIRECT WHATSAPP
   - solo se esiste token e telefono valido
========================================================== */
if ($token !== null && $telefonoRaw !== '') {

  // pulizia numero (solo cifre)
  $telefono = preg_replace('/[^0-9]/', '', $telefonoRaw);

  if (strlen($telefono) >= 9) {

    // prefisso Italia automatico
    if (!str_starts_with($telefono, '39')) {
      $telefono = '39' . $telefono;
    }

    $grattaLink = BASE_URL_FULL . '/gratta.php?token=' . $token;

    $messaggio =
      "🎁 Hai ricevuto un gratta e vinci!\n\n"
      . "Gratta qui 👉 $grattaLink";

    $waUrl =
      'https://wa.me/' . $telefono
      . '?text=' . urlencode($messaggio);

    header('Location: ' . $waUrl);
    exit;
  }
}


/* ==========================================================
   BLOCCO 7 — FALLBACK FINALE
   - nessun telefono
   - oppure gratta disattivato
========================================================== */
header('Location: ' . BASE_URL . '/admin/index.php');
exit;