<?php
declare(strict_types=1);

/* ==========================================================
   MODULO RISCATTI
   QUERY
========================================================== */

/* ==========================================================
   LISTA RISCATTI
========================================================== */
if (!function_exists('riscatti_lista')) {
    function riscatti_lista(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT
                r.id,
                r.data_riscatto,
                r.riscattato,
                p.nome AS premio,
                u.nome AS cliente,
                c.codice_carta
             FROM riscatti_premi r
             JOIN premi p ON p.id = r.premio_id
             JOIN carte_fedelta c ON c.id = r.carta_id
             JOIN utenti u ON u.id = c.utente_id
             ORDER BY r.data_riscatto DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
