<?php
declare(strict_types=1);

/* ==========================================================
   MODULO SCANSIONI - ROUTER
========================================================== */

/* esegue logica */
require __DIR__ . '/actions.php';

/* solo mobile */
if (!is_mobile_device()) {

    $titolo = 'Scanner';
    require ROOT_PATH . '/themes/semplice/header.php';
    ?>

    <h2>Scanner non disponibile</h2>
    <p>Lo scanner è utilizzabile solo da dispositivo mobile.</p>

    <?php
    require ROOT_PATH . '/themes/semplice/footer.php';
    return;
}

/* su mobile carica scanner kiosk */
require __DIR__ . '/view_kiosk.php';
