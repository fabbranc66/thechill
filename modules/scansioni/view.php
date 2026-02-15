<?php
declare(strict_types=1);

/* ==========================================================
   MODULO SCANSIONI - ROUTER
========================================================== */

/* esegue logica */
require __DIR__ . '/actions.php';

/* recupero setting scanner desktop */
$scanner_desktop = $SETTINGS['scanner_desktop'] ?? '0';

/* se non mobile e scanner desktop disattivato → blocco */
if (!is_mobile_device() && $scanner_desktop !== '1') {

    $titolo = 'Scanner';
    require ROOT_PATH . '/themes/semplice/header.php';
    ?>

    <h2>Scanner non disponibile</h2>
    <p>Lo scanner è utilizzabile solo da dispositivo mobile.</p>

    <?php
    require ROOT_PATH . '/themes/semplice/footer.php';
    return;
}

/* routing vista */
$vista = $_GET['vista'] ?? 'kiosk';

if ($vista === 'cassa') {
    require __DIR__ . '/view_cassa.php';
} else {
    require __DIR__ . '/view_kiosk.php';
}
