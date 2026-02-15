<?php
declare(strict_types=1);

/* ==========================================================
   LISTA CARTE
========================================================== */
if (!function_exists('carte_lista')) {
    function carte_lista(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT
                c.id,
                c.codice_carta,
                c.punti,
                u.nome,
                u.email,
                u.telefono,
                u.token_accesso,
                EXISTS (
                    SELECT 1
                    FROM premi p
                    WHERE p.attivo = 1
                      AND c.punti >= p.punti_necessari
                ) AS ha_premio
             FROM carte_fedelta c
             JOIN utenti u ON u.id = c.utente_id
             ORDER BY u.nome ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
