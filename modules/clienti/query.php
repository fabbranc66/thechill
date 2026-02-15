<?php
declare(strict_types=1);

/* ==========================================================
   LISTA CLIENTI
========================================================== */
if (!function_exists('clienti_lista')) {
    function clienti_lista(PDO $pdo): array
    {
        return $pdo->query(
            "SELECT id, nome, email, telefono, token_accesso
             FROM utenti
             WHERE ruolo = 'cliente'
             ORDER BY nome"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}

/* ==========================================================
   CLIENTE PER ID
========================================================== */
if (!function_exists('clienti_by_id')) {
    function clienti_by_id(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT *
             FROM utenti
             WHERE id = ?
             AND ruolo = 'cliente'
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }
}

/* ==========================================================
   CLIENTE PER TOKEN
========================================================== */
if (!function_exists('clienti_by_token')) {
    function clienti_by_token(PDO $pdo, string $token): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT *
             FROM utenti
             WHERE token_accesso = ?
             AND ruolo = 'cliente'
             LIMIT 1"
        );
        $stmt->execute([$token]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $res ?: null;
    }
}
