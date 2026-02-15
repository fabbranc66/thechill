<?php
declare(strict_types=1);

/* ==========================================================
   MODULO GESTIONE MODULI
   ACTIONS
   ----------------------------------------------------------
   - accesso solo amministratore
   - installa, attiva, disattiva, disinstalla moduli
========================================================== */

richiedi_ruolo('amministratore');

$messaggio = null;
$errore = null;

try {

    /* ======================================================
       INSTALLAZIONE MODULO
    ====================================================== */
    if (isset($_GET['installa'])) {
        $moduleManager->install($_GET['installa']);
        $messaggio = "Modulo installato con successo";
    }

    /* ======================================================
       DISINSTALLAZIONE MODULO
    ====================================================== */
    if (isset($_GET['disinstalla'])) {
        $moduleManager->uninstall($_GET['disinstalla']);
        $messaggio = "Modulo disinstallato";
    }

    /* ======================================================
       ATTIVAZIONE MODULO
    ====================================================== */
    if (isset($_GET['attiva'])) {
        $moduleManager->activate($_GET['attiva']);
        $messaggio = "Modulo attivato";
    }

    /* ======================================================
       DISATTIVAZIONE MODULO
    ====================================================== */
    if (isset($_GET['disattiva'])) {
        $moduleManager->deactivate($_GET['disattiva']);
        $messaggio = "Modulo disattivato";
    }

} catch (Throwable $e) {

    /* ======================================================
       GESTIONE ERRORI
    ====================================================== */
    $errore = $e->getMessage();
}
