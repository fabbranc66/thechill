<?php
declare(strict_types=1);

/* ==========================================================
   AZIONE SCANSIONE
========================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codice'])) {

    header('Content-Type: application/json');

    $codice = trim($_POST['codice']);

    if ($codice === '') {
        echo json_encode(['ok' => false, 'msg' => 'Codice vuoto']);
        exit;
    }

    /* estrae token se è un link */
    if (strpos($codice, 't=') !== false) {
        parse_str(parse_url($codice, PHP_URL_QUERY), $params);
        $codice = $params['t'] ?? '';
    }

    if ($codice === '') {
        echo json_encode(['ok' => false, 'msg' => 'Token non valido']);
        exit;
    }

    /* recupera carta */
    $stmt = $pdo->prepare(
        "SELECT c.id, c.punti, u.nome
         FROM carte_fedelta c
         JOIN utenti u ON u.id = c.utente_id
         WHERE u.token_accesso = ?
         LIMIT 1"
    );
    $stmt->execute([$codice]);

    $carta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$carta) {
        echo json_encode(['ok' => false, 'msg' => 'Carta non trovata']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        /* incremento punti (placeholder) */
        $pdo->prepare(
            "UPDATE carte_fedelta
             SET punti = punti + 1
             WHERE id = ?"
        )->execute([$carta['id']]);

        /* log scansione */
        $pdo->prepare(
            "INSERT INTO log_scansioni (carta_id, punti, origine)
             VALUES (?, 1, 'scanner')"
        )->execute([$carta['id']]);

        /* placeholder gratta */
        $msg = 'Scansione registrata per ' . $carta['nome'] . ' (+1 punto)';

        $pdo->commit();

        echo json_encode([
            'ok' => true,
            'msg' => $msg
        ]);

    } catch (Throwable $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        echo json_encode([
            'ok' => false,
            'msg' => 'Errore scansione'
        ]);
    }

    exit;
}
