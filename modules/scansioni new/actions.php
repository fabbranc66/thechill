<?php
declare(strict_types=1);
$messaggio = null;
$errore = null;

function esegui_scansione(PDO $pdo, string $codice): array
{
    $codice = trim($codice);
    if ($codice === '') return ['errore' => 'Codice vuoto'];

    $token = $codice;
    if (strpos($codice, 't=') !== false) {
        parse_str(parse_url($codice, PHP_URL_QUERY), $params);
        $token = $params['t'] ?? '';
    }
    if ($token === '') return ['errore' => 'Token non valido'];

    $stmt = $pdo->prepare(
        "SELECT c.id AS carta_id, c.punti, u.nome
         FROM carte_fedelta c
         JOIN utenti u ON u.id = c.utente_id
         WHERE u.token_accesso = ?
         LIMIT 1"
    );
    $stmt->execute([$token]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cliente) return ['errore' => 'Carta non trovata'];

    $carta_id = (int)$cliente['carta_id'];

    $pdo->prepare(
        "UPDATE carte_fedelta SET punti = punti + 1 WHERE id = ?"
    )->execute([$carta_id]);

    $token_gratta = bin2hex(random_bytes(8));
    $pdo->prepare(
        "INSERT INTO gratta_vinci
         (carta_id, token, premio_punti, vincente, grattato, riscattato, creato_il)
         VALUES (?, ?, 0, 0, 0, 0, NOW())"
    )->execute([$carta_id, $token_gratta]);

    return ['ok' => 'Scansione registrata'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codice = $_POST['codice'] ?? '';
    $result = esegui_scansione($pdo, $codice);
    if (isset($result['errore'])) $errore = $result['errore'];
    else $messaggio = $result['ok'];
}
