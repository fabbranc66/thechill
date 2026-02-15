<?php
declare(strict_types=1);

if (function_exists('scansioni_totali')) {
    return;
}

/* ==========================================================
   SCANSIONI TOTALI
========================================================== */
function scansioni_totali(PDO $pdo): int
{
    return (int)$pdo->query(
        "SELECT COUNT(*) FROM scansioni"
    )->fetchColumn();
}
