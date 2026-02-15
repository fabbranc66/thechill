<?php
declare(strict_types=1);

/* evita doppia definizione */
if (!function_exists('premi_lista')) {

    /* ==========================================================
       LISTA PREMI
    ========================================================== */
    function premi_lista(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT id, nome, punti_necessari, attivo
             FROM premi
             ORDER BY punti_necessari ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ==========================================================
       CARICA PREMIO
    ========================================================== */
    function premi_carica(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT id, nome, punti_necessari, attivo
             FROM premi
             WHERE id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
       CARICA CARTA PER RISCATTO
    ========================================================== */
    function premi_carica_carta(PDO $pdo, int $carta_id): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT
                c.id,
                c.codice_carta,
                c.punti,
                u.nome
             FROM carte_fedelta c
             JOIN utenti u ON u.id = c.utente_id
             WHERE c.id = ?
             LIMIT 1"
        );
        $stmt->execute([$carta_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ==========================================================
       PREMI RISCATTABILI
    ========================================================== */
    function premi_riscattabili(PDO $pdo, int $punti): array
    {
        $stmt = $pdo->prepare(
            "SELECT id, nome, punti_necessari
             FROM premi
             WHERE attivo = 1
               AND punti_necessari <= ?
             ORDER BY punti_necessari ASC"
        );
        $stmt->execute([$punti]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
