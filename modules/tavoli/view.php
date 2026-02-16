<?php
declare(strict_types=1);

/* ==========================================================
   ROUTER MODULO TAVOLI
   - admin: dashboard
   - pubblico: lista tavoli
========================================================== */

if (
    isset($_SESSION['utente']) &&
    ($_SESSION['utente']['ruolo'] ?? '') === 'amministratore'
) {
    require __DIR__ . '/index.php';
    return;
}

/* pubblico */
require __DIR__ . '/public.php';
