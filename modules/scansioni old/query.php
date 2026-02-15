<?php
declare(strict_types=1);

/* evita doppia definizione */
if (function_exists('scansioni_lista')) {
    return;
}

/* ==========================================================
   LISTA SCANSIONI
========================================================== */
function scansioni_lista(PDO $pdo): array
{
    return $pdo->query(
        "SELECT
            l.id,
            l.carta_id,
            l.punti,
            l.origine,
            l.data_scansione,
            u.nome
         FROM log_scansioni l
         JOIN carte_fedelta c ON c.id = l.carta_id
         JOIN utenti u ON u.id = c.utente_id
         ORDER BY l.data_scansione DESC"
    )->fetchAll(PDO::FETCH_ASSOC);
}

/* ==========================================================
   CONTEGGIO SCANSIONI
========================================================== */
function scansioni_totali(PDO $pdo): int
{
    return (int)$pdo->query(
        "SELECT COUNT(*) FROM log_scansioni"
    )->fetchColumn();
}