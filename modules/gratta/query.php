<?php
declare(strict_types=1);

/* ==========================================================
   MODULO GRATTA
   QUERY
========================================================== */

/* ==========================================================
   LISTA GRATTA E VINCI
========================================================== */
if (!function_exists('gratta_lista')) {
    function gratta_lista(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT
                g.id,
                g.premio_punti,
                g.vincente,
                g.grattato,
                g.riscattato,
                g.creato_il,
                u.nome AS cliente
             FROM gratta_vinci g
             JOIN carte_fedelta c ON c.id = g.carta_id
             JOIN utenti u ON u.id = c.utente_id
             ORDER BY g.creato_il DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
