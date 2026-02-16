<?php
declare(strict_types=1);

/* ==========================================================
   MODULO SCANSIONI
   QUERY
========================================================== */

/* ==========================================================
   LISTA SCANSIONI
========================================================== */
if (!function_exists('scansioni_lista')) {
    function scansioni_lista(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT
                l.id,
                l.carta_id,
                l.punti,
                l.data_scansione,
                c.codice_carta
             FROM log_scansioni l
             JOIN carte_fedelta c ON c.id = l.carta_id
             ORDER BY l.data_scansione DESC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}

/* ==========================================================
   SCANSIONI TOTALI (dashboard)
========================================================== */
if (!function_exists('scansioni_totali')) {
    function scansioni_totali(PDO $pdo): int
    {
        return (int)$pdo->query(
            "SELECT COUNT(*) FROM log_scansioni"
        )->fetchColumn();
    }
}

/* ==========================================================
   RECUPERA CARTA DA TOKEN
========================================================== */
if (!function_exists('scansioni_carta_da_token')) {
    function scansioni_carta_da_token(PDO $pdo, string $token): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT
                c.id,
                c.punti,
                u.nome
             FROM utenti u
             JOIN carte_fedelta c ON c.utente_id = u.id
             WHERE u.token_accesso = ?
             LIMIT 1"
        );
        $stmt->execute([$token]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

/* ==========================================================
   REGISTRA SCANSIONE
========================================================== */
if (!function_exists('scansione_registra')) {
    function scansione_registra(
        PDO $pdo,
        int $carta_id,
        int $punti
    ): void {

        $pdo->beginTransaction();

        try {
            /* incrementa punti */
            $stmt = $pdo->prepare(
                "UPDATE carte_fedelta
                 SET punti = punti + ?
                 WHERE id = ?"
            );
            $stmt->execute([$punti, $carta_id]);

            /* log scansione */
            $stmt = $pdo->prepare(
                "INSERT INTO log_scansioni
                 (carta_id, punti, origine, data_scansione)
                 VALUES (?, ?, 'operatore', NOW())"
            );
            $stmt->execute([$carta_id, $punti]);

            $pdo->commit();

        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
