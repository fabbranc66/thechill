<?php
// ==========================================================
// FILE: admin/invia_qr_whatsapp.php
// RUOLO:
// - invio carta fedeltà via WhatsApp
// - usa link cliente con token
// ==========================================================

declare(strict_types=1);

/* ==========================================================
   1. BOOTSTRAP
========================================================== */
require __DIR__ . '/../includes/init.php';
require __DIR__ . '/../includes/auth.php';

/* ==========================================================
   2. SICUREZZA
========================================================== */
richiedi_ruolo('amministratore');

/* ==========================================================
   3. INPUT
========================================================== */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    exit('ID carta non valido');
}

/* ==========================================================
   4. RECUPERO DATI CLIENTE
   - nome
   - telefono
   - token_accesso
========================================================== */
$stmt = $pdo->prepare(
    "SELECT u.nome, u.telefono, u.token_accesso
     FROM carte_fedelta c
     JOIN utenti u ON u.id = c.utente_id
     WHERE c.id = ?
     LIMIT 1"
);
$stmt->execute([$id]);

$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

/* ==========================================================
   5. VALIDAZIONE
========================================================== */
if (!$cliente || empty($cliente['telefono'])) {
    exit('Telefono non disponibile');
}

/* ==========================================================
   6. NORMALIZZAZIONE TELEFONO
========================================================== */
$telefono = preg_replace('/[^0-9]/', '', $cliente['telefono']);

if (strlen($telefono) === 10) {
    $telefono = '39' . $telefono;
}

/* ==========================================================
   7. TOKEN CLIENTE (SE MANCA, CREALO)
========================================================== */
$token = $cliente['token_accesso'];

if (empty($token)) {

    $token = bin2hex(random_bytes(16));

    $stmt = $pdo->prepare(
        "UPDATE utenti
         SET token_accesso = ?
         WHERE telefono = ?
         LIMIT 1"
    );
    $stmt->execute([$token, $cliente['telefono']]);
}

/* ==========================================================
   LINK CLIENTE
========================================================== */
$linkCliente = BASE_URL_FULL
    . '/cliente.php?t='
    . $token;

/* ==========================================================
   8. MESSAGGIO WHATSAPP
========================================================== */
$messaggio = sprintf(
    "Ciao %s! 👋\n\n"
    . "Ecco la tua tessera fedeltà digitale CheersClub 🎉\n\n"
    . "%s\n\n"
    . "Salvala tra i preferiti e mostrala in cassa per accumulare punti!",
    $cliente['nome'],
    $linkCliente
);

/* ==========================================================
   9. REDIRECT A WHATSAPP
========================================================== */
$url = 'https://wa.me/' . $telefono . '?text=' . urlencode($messaggio);

header('Location: ' . $url);
exit;
